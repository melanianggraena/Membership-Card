@extends('layouts.app')

@section('title', 'Riwayat & Laporan Transaksi')

@section('content')
<div class="page-head">
    <div>
        <h1>Riwayat & Laporan Transaksi</h1>
        <p>Pantau, filter transaksi berdasarkan tanggal, nominal, dan outlet, serta ekspor laporan ke CSV.</p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a class="btn btn-secondary" href="{{ route('transactions.export', request()->query()) }}" style="border-color: #16a34a; color: #16a34a;">
            <i data-lucide="file-spreadsheet"></i> Export CSV
        </a>
        <a class="btn btn-primary" href="{{ route('outlet-transactions.create') }}">
            <i data-lucide="credit-card"></i> Transaksi Outlet
        </a>
    </div>
</div>

<form class="card" method="GET" style="margin-bottom: 24px; padding: 22px;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; align-items: end;">
        <div>
            <label style="font-size: 12px; font-weight: 600; color: #555; display: block; margin-bottom: 6px;">Pencarian Member / Kode</label>
            <div class="search-box" style="width: 100%;">
                <i data-lucide="search"></i>
                <input name="search" value="{{ request('search') }}" placeholder="Kode trx, nama/kode member...">
            </div>
        </div>

        <div>
            <label style="font-size: 12px; font-weight: 600; color: #555; display: block; margin-bottom: 6px;">Jenis Transaksi</label>
            <select name="type">
                <option value="">Semua Jenis</option>
                <option value="top_up" @selected(request('type')==='top_up')>Top Up Saldo</option>
                <option value="room_access" @selected(request('type')==='room_access')>Akses Ruangan</option>
                <option value="outlet_purchase" @selected(request('type')==='outlet_purchase')>Pembelian Outlet</option>
            </select>
        </div>

        <div>
            <label style="font-size: 12px; font-weight: 600; color: #555; display: block; margin-bottom: 6px;">Kategori Outlet</label>
            <select name="outlet_id">
                <option value="">Semua Outlet</option>
                @foreach($outlets as $outlet)
                    <option value="{{ $outlet->id }}" @selected(request('outlet_id') == $outlet->id)>{{ $outlet->outlet_name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label style="font-size: 12px; font-weight: 600; color: #555; display: block; margin-bottom: 6px;">Status Transaksi</label>
            <select name="status">
                <option value="">Semua Status</option>
                <option value="success" @selected(request('status')==='success')>Berhasil</option>
                <option value="failed" @selected(request('status')==='failed')>Gagal</option>
            </select>
        </div>

        <div>
            <label style="font-size: 12px; font-weight: 600; color: #555; display: block; margin-bottom: 6px;">Dari Tanggal</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}">
        </div>

        <div>
            <label style="font-size: 12px; font-weight: 600; color: #555; display: block; margin-bottom: 6px;">Sampai Tanggal</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}">
        </div>

        <div>
            <label style="font-size: 12px; font-weight: 600; color: #555; display: block; margin-bottom: 6px;">Nominal Minimal (Rp)</label>
            <input type="number" name="min_amount" value="{{ request('min_amount') }}" placeholder="Contoh: 10000" min="0">
        </div>

        <div>
            <label style="font-size: 12px; font-weight: 600; color: #555; display: block; margin-bottom: 6px;">Nominal Maksimal (Rp)</label>
            <input type="number" name="max_amount" value="{{ request('max_amount') }}" placeholder="Contoh: 500000" min="0">
        </div>
    </div>

    <div style="display: flex; gap: 10px; justify-content: flex-end; align-items: center; margin-top: 18px; padding-top: 16px; border-top: 1px solid #f3e5e7; flex-wrap: wrap;">
        @if(request()->anyFilled(['search', 'type', 'outlet_id', 'status', 'start_date', 'end_date', 'min_amount', 'max_amount']))
            <a href="{{ route('transactions.index') }}" class="btn btn-secondary" style="color: #666;">
                <i data-lucide="rotate-ccw"></i> Reset Filter
            </a>
        @endif
        <button type="submit" class="btn btn-primary">
            <i data-lucide="filter"></i> Terapkan Filter
        </button>
        <a href="{{ route('transactions.export', request()->query()) }}" class="btn btn-secondary" style="border-color: #16a34a; color: #16a34a;">
            <i data-lucide="download"></i> Download CSV
        </a>
    </div>
</form>

<section class="card">
    <div class="card-head">
        <h2>Daftar Transaksi ({{ $transactions->total() }})</h2>
        <span style="font-size: 13px; color: var(--muted);">
            Menampilkan data halaman {{ $transactions->currentPage() }} dari {{ $transactions->lastPage() }}
        </span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Kode Transaksi</th>
                    <th>Member</th>
                    <th>Jenis</th>
                    <th>Outlet / Ruangan</th>
                    <th>Promo</th>
                    <th>Diskon</th>
                    <th>Total</th>
                    <th>Saldo</th>
                    <th>Status</th>
                    <th>Admin/Kasir</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $trx)
                    <tr>
                        <td>
                            <a class="link" href="{{ route('transactions.show', $trx) }}">
                                {{ $trx->transaction_code }}
                            </a>
                        </td>
                        <td>
                            <b>{{ $trx->member?->full_name ?? '-' }}</b>
                            <small>{{ $trx->member?->member_code }}</small>
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
                        <td>{{ $trx->outlet?->outlet_name ?? $trx->room?->room_name ?? '-' }}</td>
                        <td>{{ $trx->promo?->title ?? '-' }}</td>
                        <td>Rp {{ number_format($trx->discount_amount, 0, ',', '.') }}</td>
                        <td><b>Rp {{ number_format($trx->amount, 0, ',', '.') }}</b></td>
                        <td>Rp {{ number_format($trx->balance_before, 0, ',', '.') }} → <b>Rp {{ number_format($trx->balance_after, 0, ',', '.') }}</b></td>
                        <td>
                            <span class="badge {{ $trx->status === 'success' ? 'success' : 'danger' }}">
                                {{ $trx->status === 'success' ? 'Berhasil' : 'Gagal' }}
                            </span>
                        </td>
                        <td>{{ $trx->admin?->name ?? '-' }}</td>
                        <td>{{ $trx->created_at->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="empty">Tidak ada transaksi yang sesuai dengan filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        {{ $transactions->links() }}
    </div>
</section>
@endsection
