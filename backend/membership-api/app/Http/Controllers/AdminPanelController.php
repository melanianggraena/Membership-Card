<?php

namespace App\Http\Controllers;

use App\Models\AccessHistory;
use App\Models\Admin;
use App\Models\Member;
use App\Models\Room;
use App\Models\TopUp;
use App\Models\Transaction;
use App\Services\AdminNotificationService;
use App\Services\TransactionCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminPanelController extends Controller
{
    public function members(Request $request)
    {
        $members = Member::query()->when($request->search, fn ($q, $s) => $q->where(fn ($q) => $q->where('full_name', 'like', "%{$s}%")->orWhere('member_code', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%")))->when($request->status, fn ($q, $s) => $q->where('status', $s))->latest()->paginate(10)->withQueryString();
        return view('members.index', compact('members'));
    }

    public function createMember() { return view('members.form'); }

    public function storeMember(Request $request)
    {
        $data = $request->validate(['full_name' => 'required|max:255', 'phone' => 'required|max:30|unique:members', 'email' => 'nullable|email|unique:members', 'nfc_uid' => 'nullable|max:255|unique:members', 'balance' => 'nullable|numeric|min:0', 'status' => ['required', Rule::in(['active', 'inactive'])], 'expired_at' => 'nullable|date']);
        $data['member_code'] = now()->format('Ym').str_pad((string) (Member::max('id') + 1), 3, '0', STR_PAD_LEFT);
        Member::create($data);
        return redirect()->route('members.index')->with('success', 'Member berhasil ditambahkan.');
    }

    public function editMember(Member $member) { return view('members.form', compact('member')); }
    public function updateMember(Request $request, Member $member)
    {
        $data = $request->validate(['full_name' => 'required|max:255', 'phone' => ['required', 'max:30', Rule::unique('members')->ignore($member)], 'email' => ['nullable', 'email', Rule::unique('members')->ignore($member)], 'nfc_uid' => ['nullable', 'max:255', Rule::unique('members')->ignore($member)], 'status' => ['required', Rule::in(['active', 'inactive'])], 'expired_at' => 'nullable|date']);
        $member->update($data);
        return redirect()->route('members.index')->with('success', 'Data member diperbarui.');
    }

    public function rooms(Request $request)
    {
        $rooms = Room::query()->when($request->status, fn ($q, $s) => $q->where('status', $s))->latest()->paginate(10)->withQueryString();
        return view('rooms.index', compact('rooms'));
    }
    public function storeRoom(Request $request)
    {
        Room::create($request->validate(['room_name' => 'required|max:255', 'description' => 'nullable', 'access_price' => 'required|numeric|min:0', 'capacity' => 'required|integer|min:1', 'status' => ['required', Rule::in(['active', 'inactive'])]]));
        return back()->with('success', 'Ruangan berhasil ditambahkan.');
    }
    public function updateRoom(Request $request, Room $room)
    {
        $room->update($request->validate(['room_name' => 'required|max:255', 'description' => 'nullable', 'access_price' => 'required|numeric|min:0', 'capacity' => 'required|integer|min:1', 'status' => ['required', Rule::in(['active', 'inactive'])]]));
        return back()->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function topUps(Request $request)
    {
        $members = Member::where('status', 'active')->orderBy('full_name')->get();
        $history = TopUp::with(['member', 'admin'])->latest()->limit(8)->get();
        $selected = $request->member ? Member::find($request->member) : null;
        return view('topups.index', compact('members', 'history', 'selected'));
    }
    public function storeTopUp(Request $request, TransactionCodeService $codes, AdminNotificationService $notifications)
    {
        $data = $request->validate(['member_id' => 'required|exists:members,id', 'amount' => 'required|numeric|min:1000', 'payment_method' => ['required', Rule::in(['cash', 'transfer', 'qris'])], 'notes' => 'nullable|max:500']);
        $transaction = DB::transaction(function () use ($data, $request, $codes) {
            $member = Member::lockForUpdate()->findOrFail($data['member_id']);
            $before = (float) $member->balance;
            $topUp = TopUp::create($data + ['admin_id' => $request->user()->id]);
            $after = $before + (float) $data['amount'];
            $member->update(['balance' => $after]);
            return Transaction::create(['transaction_code' => $codes->next(), 'member_id' => $member->id, 'admin_id' => $request->user()->id, 'transaction_type' => 'top_up', 'reference_id' => $topUp->id, 'amount' => $data['amount'], 'balance_before' => $before, 'balance_after' => $after, 'status' => 'success']);
        });
        $transaction->load('member');
        $notifications->send('top_up', 'Top Up berhasil', "Member {$transaction->member->member_code} melakukan top up Rp ".number_format((float) $transaction->amount, 0, ',', '.')." ({$transaction->transaction_code}).", route('transactions.show', $transaction));
        return back()->with('success', 'Top up saldo berhasil diproses.');
    }

    public function scan() { return view('scan.index', ['rooms' => Room::where('status', 'active')->get()]); }
    public function scanStore(Request $request, TransactionCodeService $codes, AdminNotificationService $notifications)
    {
        $data = $request->validate(['uid' => 'required', 'room_id' => 'required|exists:rooms,id']);
        $result = DB::transaction(function () use ($data, $request, $codes) {
            $room = Room::lockForUpdate()->findOrFail($data['room_id']);
            $member = Member::where('nfc_uid', $data['uid'])->lockForUpdate()->first();
            $success = $member && $member->status === 'active' && $room->status === 'active' && (float) $member->balance >= (float) $room->access_price;
            $reason = ! $member ? 'Kartu tidak terdaftar' : ($member->status !== 'active' ? 'Member tidak aktif' : ($room->status !== 'active' ? 'Ruangan tidak aktif' : ((float) $member->balance < (float) $room->access_price ? 'Saldo tidak mencukupi' : 'Akses diberikan')));
            $access = AccessHistory::create(['member_id' => $member?->id, 'room_id' => $room->id, 'uid' => $data['uid'], 'access_status' => $success ? 'success' : 'failed', 'reason' => $reason, 'scanned_at' => now()]);
            $transaction = null;
            if ($success) {
                $before = (float) $member->balance;
                $after = $before - (float) $room->access_price;
                $member->update(['balance' => $after, 'last_used' => now(), 'expired_at' => now()->addYear()]);
                $transaction = Transaction::create(['transaction_code' => $codes->next(), 'member_id' => $member->id, 'admin_id' => $request->user()->id, 'room_id' => $room->id, 'transaction_type' => 'room_access', 'reference_id' => $access->id, 'amount' => $room->access_price, 'balance_before' => $before, 'balance_after' => $after, 'status' => 'success']);
            }
            return compact('member', 'room', 'success', 'reason', 'transaction');
        });
        $memberCode = $result['member']?->member_code ?? $data['uid'];
        $notifications->send('nfc_access', $result['success'] ? 'Akses NFC berhasil' : 'Akses NFC ditolak', "{$memberCode}: {$result['reason']} di {$result['room']->room_name}.", $result['transaction'] ? route('transactions.show', $result['transaction']) : route('accesses.index'));
        return back()->with($result['success'] ? 'success' : 'error', $result['reason']);
    }

    public function transactions(Request $request) { $transactions = Transaction::with(['member', 'admin', 'room', 'outlet'])->when($request->type, fn ($q, $v) => $q->where('transaction_type', $v))->when($request->status, fn ($q, $v) => $q->where('status', $v))->when($request->search, fn ($q, $v) => $q->where(fn ($q) => $q->where('transaction_code', 'like', "%{$v}%")->orWhereHas('member', fn ($q) => $q->where('member_code', 'like', "%{$v}%")->orWhere('full_name', 'like', "%{$v}%"))))->latest()->paginate(12)->withQueryString(); return view('transactions.index', compact('transactions')); }
    public function transaction(Transaction $transaction) { $transaction->load(['member', 'admin', 'room', 'outlet']); return view('transactions.show', compact('transaction')); }
    public function accesses(Request $request) { $accesses = AccessHistory::with(['member', 'room'])->when($request->status, fn ($q, $v) => $q->where('access_status', $v))->latest('scanned_at')->paginate(12)->withQueryString(); return view('accesses.index', compact('accesses')); }

    public function admins() { return view('admins.index', ['admins' => Admin::latest()->paginate(10)]); }
    public function storeAdmin(Request $request) { $data = $request->validate(['name' => 'required|max:255', 'email' => 'required|email|unique:admins', 'password' => 'required|min:8|confirmed', 'role' => ['required', Rule::in(['admin', 'cashier'])]]); Admin::create($data); return back()->with('success', 'Admin berhasil ditambahkan.'); }
}
