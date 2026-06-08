@extends('layouts/contentNavbarLayout')
@section('title', 'Pengguna')

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible mb-6">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Manajemen Pengguna</h5>
                <a href="{{ route('pengguna.create') }}" class="btn btn-primary"><i class="icon-base bx bx-plus me-1"></i> Tambah Pengguna</a>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead><tr><th>#</th><th>Nama</th><th>Email</th><th>Role</th><th>Bergabung</th><th>Aksi</th></tr></thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($users as $user)
                        <tr>
                            <td>{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar avatar-sm">
                                        <span class="avatar-initial rounded-circle bg-label-primary">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    </div>
                                    <span class="fw-medium">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{!! $user->role?->name ? e($user->role->name) : '<span class="text-muted">-</span>' !!}</td>
                            <td>{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="icon-base bx bx-dots-vertical-rounded"></i></button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('pengguna.show', $user) }}"><i class="icon-base bx bx-show me-1"></i> Detail</a>
                                        <a class="dropdown-item" href="{{ route('pengguna.edit', $user) }}"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                                        <form action="{{ route('pengguna.destroy', $user) }}" method="POST" data-confirm="Hapus pengguna ini?">
                                            @csrf @method('DELETE')
                                            <button class="dropdown-item text-danger"><i class="icon-base bx bx-trash me-1"></i> Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-6 text-muted">Belum ada pengguna. <a href="{{ route('pengguna.create') }}">Tambah sekarang</a>.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())<div class="card-footer d-flex justify-content-end">{{ $users->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
