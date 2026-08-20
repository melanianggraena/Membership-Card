@extends('layouts.app')
@section('title','Promo')
@section('content')
<div class="page-head"><div><h1>Promo</h1><p>Kelola promo dan penawaran yang tampil di aplikasi member.</p></div><a class="btn btn-primary" href="{{ route('promos.create') }}"><i data-lucide="plus"></i> Tambah Promo</a></div>
<form class="toolbar" method="GET"><select name="status" onchange="this.form.submit()"><option value="">Semua status</option><option value="active" @selected(request('status')==='active')>Aktif</option><option value="inactive" @selected(request('status')==='inactive')>Tidak aktif</option></select></form>
<div class="card"><div class="table-wrap"><table><thead><tr><th>Promo</th><th>Periode</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
@forelse($promos as $promo)<tr><td><strong>{{ $promo->title }}</strong><small>{{ Str::limit($promo->description,70) }}</small></td><td>{{ $promo->start_date->format('d/m/Y') }} – {{ $promo->end_date->format('d/m/Y') }}</td><td><span class="badge {{ $promo->status==='active' ? 'success':'danger' }}">{{ $promo->status==='active' ? 'Aktif':'Tidak aktif' }}</span></td><td><div style="display:flex;gap:8px"><a class="btn btn-secondary" href="{{ route('promos.edit',$promo) }}">Edit</a><form method="POST" action="{{ route('promos.destroy',$promo) }}" onsubmit="return confirm('Hapus promo ini?')">@csrf @method('DELETE')<button class="btn btn-secondary" type="submit">Hapus</button></form></div></td></tr>
@empty<tr><td colspan="4" class="empty">Belum ada promo.</td></tr>@endforelse
</tbody></table></div><div class="pagination-wrap">{{ $promos->links() }}</div></div>
@endsection
