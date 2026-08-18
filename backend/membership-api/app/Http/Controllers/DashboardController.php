<?php

namespace App\Http\Controllers;

use App\Models\AccessHistory;
use App\Models\Member;
use App\Models\Room;
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
            ],
            'transactions' => Transaction::with(['member', 'room'])->latest()->limit(5)->get(),
            'accesses' => AccessHistory::with(['member', 'room'])->latest('scanned_at')->limit(4)->get(),
        ]);
    }
}
