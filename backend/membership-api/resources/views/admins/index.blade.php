@extends('layouts.app')

@section('title', 'Manajemen Admin')

@section('content')

<div class="page-head">
    <div>
        <h1>Manajemen Admin</h1>
        <p>Kelola akun administrator dan kasir.</p>
    </div>

    <button class="btn btn-primary" data-modal-open="adminModal">
        <i data-lucide="user-plus"></i>
        Tambah Admin
    </button>
</div>

<section class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Dibuat</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @foreach($admins as $admin)
                    <tr>
                        <td>
                            <div class="member-cell">
                                <span class="avatar">
                                    {{ strtoupper(substr($admin->name, 0, 1)) }}
                                </span>
                                <b>{{ $admin->name }}</b>
                            </div>
                        </td>

                        <td>
                            {{ $admin->email }}
                        </td>

                        <td>
                            <span class="badge info">
                                {{ ucfirst($admin->role) }}
                            </span>
                        </td>

                        <td>
                            {{ $admin->created_at->format('d M Y') }}
                        </td>

                        <td>
                            <span class="badge success">Aktif</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">
        {{ $admins->links() }}
    </div>
</section>

@endsection

@push('modals')

<div class="modal" id="adminModal">
    <div class="modal-card">

        <div class="card-head">
            <h2>Tambah Admin</h2>

            <button class="icon-btn" data-modal-close>
                <i data-lucide="x"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('admins.store') }}">
            @csrf

            <label>
                Nama
                <input name="name" required>
            </label>

            <label>
                Email
                <input type="email" name="email" required>
            </label>

            <label>
                Role
                <select name="role">
                    <option value="cashier">Cashier</option>
                    <option value="admin">Admin</option>
                </select>
            </label>

            <label>
                Password
                <input type="password" name="password" required>
            </label>

            <label>
                Konfirmasi Password
                <input type="password" name="password_confirmation" required>
            </label>

            <div class="form-actions">
                <button
                    type="button"
                    class="btn btn-secondary"
                    data-modal-close
                >
                    Batal
                </button>

                <button class="btn btn-primary">
                    Simpan Admin
                </button>
            </div>
        </form>

    </div>
</div>

@endpush
