<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Outlet;
use App\Models\Transaction;
use App\Services\AdminNotificationService;
use App\Services\TransactionCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class OutletTransactionController extends Controller
{
    public function create(Request $request): View
    {
        return view('outlet-transactions.create', [
            'outlets' => Outlet::where('status', 'active')->orderBy('outlet_name')->get(),
            'members' => Member::where('status', 'active')->orderBy('full_name')->get(),
            'selectedMember' => $request->member ? Member::find($request->member) : null,
        ]);
    }

    public function store(Request $request, TransactionCodeService $codes, AdminNotificationService $notifications): RedirectResponse
    {
        $data = $request->validate(['outlet_id' => ['required', 'exists:outlets,id'], 'member_id' => ['required', 'exists:members,id'], 'amount' => ['required', 'numeric', 'gt:0']]);

        try {
            $transaction = DB::transaction(function () use ($data, $request, $codes): Transaction {
                $outlet = Outlet::lockForUpdate()->findOrFail($data['outlet_id']);
                $member = Member::lockForUpdate()->findOrFail($data['member_id']);
                if ($outlet->status !== 'active') abort(422, 'Outlet sedang tidak aktif.');
                if ($member->status !== 'active') abort(422, 'Member sedang tidak aktif.');

                $before = (float) $member->balance;
                $success = $before >= (float) $data['amount'];
                $transaction = Transaction::create([
                    'transaction_code' => $codes->next(), 'member_id' => $member->id, 'admin_id' => $request->user()->id,
                    'outlet_id' => $outlet->id, 'transaction_type' => 'outlet_purchase', 'reference_id' => $outlet->id,
                    'amount' => $data['amount'], 'balance_before' => $before,
                    'balance_after' => $success ? $before - (float) $data['amount'] : $before,
                    'status' => $success ? 'success' : 'failed',
                ]);
                if (! $success) return $transaction;
                $member->update(['balance' => $transaction->balance_after]);
                return $transaction;
            });
        } catch (Throwable $exception) {
            report($exception);
            return back()->withInput()->with('error', $exception->getMessage() === 'Outlet sedang tidak aktif.' ? $exception->getMessage() : 'Transaksi gagal diproses. Tidak ada saldo yang dipotong.');
        }

        $transaction->load(['member', 'outlet']);
        if ($transaction->status === 'failed') {
            $notifications->send('transaction', 'Transaksi gagal', "Saldo {$transaction->member->member_code} tidak mencukupi untuk transaksi {$transaction->transaction_code}.", route('transactions.show', $transaction));
            return redirect()->route('transactions.show', $transaction)->with('error', 'Saldo member tidak mencukupi. Tidak ada saldo yang dipotong.');
        }

        $notifications->send('transaction', 'Pembelian outlet berhasil', "{$transaction->member->member_code} bertransaksi di {$transaction->outlet->outlet_name} ({$transaction->transaction_code}).", route('transactions.show', $transaction));
        return redirect()->route('transactions.show', $transaction)->with('success', 'Transaksi berhasil diproses.');
    }
}
