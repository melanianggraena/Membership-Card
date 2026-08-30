@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<section class="hero">
    <div>
        <h1>
            Halo {{ explode(' ', auth()->user()->name)[0] }},
        </h1>

        <p>
            Selamat datang di dashboard Technolife. Berikut ringkasan aktivitas membership hari ini.
        </p>
    </div>

    <i data-lucide="shield-check"></i>
</section>

<div class="stats-grid">
    @foreach([
        ['users', 'Total Member', $stats['members'], 'rose'],
        ['user-check', 'Member Aktif', $stats['activeMembers'], 'green'],
        ['wallet-cards', 'Total Saldo Member', 'Rp '.number_format($stats['balance'], 0, ',', '.'), 'blue'],
        ['receipt-text', 'Transaksi Hari Ini', $stats['transactionsToday'], 'purple']
    ] as [$icon, $label, $value, $color])

        <article class="stat-card">
            <span class="stat-icon {{ $color }}">
                <i data-lucide="{{ $icon }}"></i>
            </span>

            <small>{{ $label }}</small>
            <strong>{{ $value }}</strong>
        </article>

    @endforeach
</div>

<div class="stats-grid three compact-stats">
    <article class="stat-card">
        <small>Total Top Up Hari Ini</small>
        <strong>
            Rp {{ number_format($stats['topUpsToday'], 0, ',', '.') }}
        </strong>
    </article>

    <article class="stat-card">
        <small>Penggunaan Ruangan Hari Ini</small>
        <strong>
            Rp {{ number_format($stats['roomToday'], 0, ',', '.') }}
        </strong>
    </article>

    <article class="stat-card">
        <small>Pembelian Outlet Hari Ini</small>
        <strong>
            Rp {{ number_format($stats['outletToday'], 0, ',', '.') }}
        </strong>
    </article>
</div>

<h2 class="section-title">
    Quick Actions
</h2>

<div class="quick-grid">
    <a href="{{ route('members.create') }}">
        <i data-lucide="user-plus"></i>
        Tambah Member
    </a>

    <a href="{{ route('topups.index') }}">
        <i data-lucide="banknote"></i>
        Top Up Saldo
    </a>

    <a href="{{ route('scan.index') }}">
        <i data-lucide="scan-line"></i>
        Scan NFC
    </a>

    <a href="{{ route('outlet-transactions.create') }}">
        <i data-lucide="credit-card"></i>
        Transaksi Outlet
    </a>
</div>

<div class="dashboard-grid">

    <section class="card">
        <div class="card-head">
            <h2>Transaksi Terbaru</h2>
            <a href="{{ route('transactions.index') }}">
                Lihat Semua
            </a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Member</th>
                        <th>Tipe</th>
                        <th>Nominal/Detail</th>
                        <th>Status</th>
                        <th>Waktu</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($transactions as $trx)
                        <tr>
                            <td>
                                <a
                                    class="link"
                                    href="{{ route('transactions.show', $trx) }}"
                                >
                                    {{ $trx->transaction_code }}
                                </a>
                            </td>

                            <td>
                                <b>
                                    {{ $trx->member?->full_name ?? 'Member dihapus' }}
                                </b>
                            </td>

                            <td>
                                <span class="badge info">
                                    {{
                                        [
                                            'top_up' => 'Top Up',
                                            'room_access' => 'Akses Ruangan',
                                            'outlet_purchase' => 'Pembelian Outlet'
                                        ][$trx->transaction_type] ?? $trx->transaction_type
                                    }}
                                </span>
                            </td>

                            <td>
                                {{
                                    $trx->outlet?->outlet_name
                                    ?? $trx->room?->room_name
                                    ?? 'Rp '.number_format($trx->amount, 0, ',', '.')
                                }}
                            </td>

                            <td>
                                <span class="badge {{ $trx->status === 'success' ? 'success':'danger' }}">
                                    {{ $trx->status === 'success' ? 'Berhasil' : 'Gagal' }}
                                </span>
                            </td>

                            <td>
                                {{ $trx->created_at->format('H:i') }}
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" class="empty">
                                Belum ada transaksi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="card">
        <div class="card-head">
            <h2>Akses Terbaru</h2>
        </div>

        <div class="activity-list">
            @forelse($accesses as $access)
                <article>
                    <span class="activity-icon">
                        <i data-lucide="{{ $access->access_status === 'success' ? 'door-open':'circle-slash' }}"></i>
                    </span>

                    <div>
                        <b>
                            {{ $access->member?->full_name ?? $access->uid }}
                        </b>

                        <p>
                            {{ $access->reason }} · {{ $access->room->room_name }}
                        </p>

                        <small>
                            {{ $access->scanned_at->diffForHumans() }}
                        </small>
                    </div>
                </article>

            @empty
                <p class="empty">
                    Belum ada riwayat akses.
                </p>
            @endforelse
        </div>
    </section>

</div>

@endsection

