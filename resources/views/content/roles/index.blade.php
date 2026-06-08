@extends('layouts/contentNavbarLayout')
@section('title', 'Manajemen Roles')

@section('content')
<div class="row">
    <div class="col-12">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible mb-6" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Manajemen Roles</h5>
                <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">
                    <i class="icon-base bx bx-plus me-1"></i> Tambah Role
                </a>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Role</th>
                            <th>Deskripsi</th>
                            <th>Tipe</th>
                            <th>Pengguna</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($roles as $i => $role)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><span class="fw-medium">{{ $role->name }}</span></td>
                            <td>{{ $role->description ?? '-' }}</td>
                            <td>
                                @if($role->is_super_admin)
                                    <span class="badge bg-label-danger">Super Admin</span>
                                @else
                                    <span class="badge bg-label-secondary">Custom</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-label-info">{{ $role->users_count }} pengguna</span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('roles.permissions', $role) }}"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Kelola Hak Akses">
                                        <i class="icon-base bx bx-shield-quarter me-1"></i> Hak Akses
                                    </a>
                                    <a href="{{ route('roles.edit', $role) }}"
                                        class="btn btn-sm btn-outline-secondary"
                                        title="Edit">
                                        <i class="icon-base bx bx-edit-alt"></i>
                                    </a>
                                    <form action="{{ route('roles.destroy', $role) }}" method="POST"
                                        data-confirm="Hapus role {{ $role->name }}?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="icon-base bx bx-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 text-muted">
                                Belum ada role. <a href="{{ route('roles.create') }}">Tambah sekarang</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
