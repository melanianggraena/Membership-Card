@php
$nav = [
 ['dashboard','dashboard','layout-dashboard','Dashboard'], ['members.*','members.index','users','Member'],
 ['rooms.*','rooms.index','door-open','Outlet'], ['topups.*','topups.index','wallet-cards','Top Up Saldo'],
 ['scan.*','scan.index','scan-line','Scan NFC'], ['transactions.*','transactions.index','receipt-text','Transaksi'],
 ['accesses.*','accesses.index','history','Riwayat Akses'], ['admins.*','admins.index','shield-user','Admin'],
 ['settings.*','settings.index','settings','Pengaturan'],
];
@endphp
<aside class="sidebar" id="sidebar">
    <div class="brand"><span class="brand-mark">T</span><div><strong>Technolife</strong><small>Admin Suite</small></div></div>
    <nav class="nav-list">
        @foreach($nav as [$pattern,$route,$icon,$label])
            @if($route !== 'admins.index' || auth()->user()->role === 'admin')
            <a href="{{ route($route) }}" class="nav-item {{ request()->routeIs($pattern) ? 'active' : '' }}"><i data-lucide="{{ $icon }}"></i><span>{{ $label }}</span></a>
            @endif
        @endforeach
    </nav>
    <div class="sidebar-bottom">
        <a class="btn btn-primary btn-block" href="{{ route('members.create') }}"><i data-lucide="plus"></i> Tambah Member</a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="nav-item logout" type="submit"><i data-lucide="log-out"></i> Logout</button></form>
    </div>
</aside>
