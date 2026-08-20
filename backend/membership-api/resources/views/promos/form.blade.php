@extends('layouts.app')
@section('title',isset($promo)?'Edit Promo':'Tambah Promo')
@section('content')
<a class="back-link" href="{{ route('promos.index') }}"><i data-lucide="arrow-left"></i>Kembali</a>
<div class="page-head"><div><h1>{{ isset($promo)?'Edit Promo':'Tambah Promo' }}</h1><p>Promo aktif dalam periodenya akan langsung tersedia di aplikasi member.</p></div></div>
<form class="form-card" method="POST" enctype="multipart/form-data" action="{{ isset($promo)?route('promos.update',$promo):route('promos.store') }}">@csrf @isset($promo) @method('PUT') @endisset
@if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif
<div class="form-grid"><label style="grid-column:1/-1">Judul<input name="title" value="{{ old('title',$promo->title??'') }}" required></label><label>Tanggal mulai<input type="date" name="start_date" value="{{ old('start_date',isset($promo)?$promo->start_date->format('Y-m-d'):'') }}" required></label><label>Tanggal selesai<input type="date" name="end_date" value="{{ old('end_date',isset($promo)?$promo->end_date->format('Y-m-d'):'') }}" required></label><label>Status<select name="status"><option value="active" @selected(old('status',$promo->status??'active')==='active')>Aktif</option><option value="inactive" @selected(old('status',$promo->status??'')==='inactive')>Tidak aktif</option></select></label><label>Banner (maks. 3 MB)<input type="file" name="image" accept="image/*"></label><label style="grid-column:1/-1">Deskripsi<textarea name="description" rows="5" required>{{ old('description',$promo->description??'') }}</textarea></label><label style="grid-column:1/-1">Syarat & Ketentuan<textarea name="terms" rows="5">{{ old('terms',$promo->terms??'') }}</textarea></label></div>
<div class="form-actions"><a class="btn btn-secondary" href="{{ route('promos.index') }}">Batal</a><button class="btn btn-primary">Simpan Promo</button></div></form>
@endsection
