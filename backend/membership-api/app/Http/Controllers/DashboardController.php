<?php

namespace App\Http\Controllers;

use App\Models\AccessHistory;
use App\Models\Member;
use App\Models\Room;
use App\Models\TopUp;
use App\Models\Transaction;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index', [
            'stats' => [
                'members' => Member::count(),
                'activeMembers' => Member::where('status', 'active')->count(),
                'balance' => Member::sum('balance'),
                'rooms' => Room::count(),
                'transactionsToday' => Transaction::whereDate('created_at', today())->count(),
                'topUpsToday' => TopUp::whereDate('created_at', today())->sum('amount'),
                'roomToday' => Transaction::where('transaction_type', 'room_access')->where('status', 'success')->whereDate('created_at', today())->sum('amount'),
                'outletToday' => Transaction::where('transaction_type', 'outlet_purchase')->where('status', 'success')->whereDate('created_at', today())->sum('amount'),
            ],
            'transactions' => Transaction::with(['member', 'room', 'outlet'])->latest()->limit(5)->get(),
            'accesses' => AccessHistory::with(['member', 'room'])->latest('scanned_at')->limit(4)->get(),
        ]);
    }
}
