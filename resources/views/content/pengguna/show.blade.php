@extends('layouts/contentNavbarLayout')
@section('title', 'Detail Pengguna')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="d-flex align-items-center justify-content-between mb-6">
            <h4 class="mb-0">Detail Pengguna</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('pengguna.edit', $pengguna) }}" class="btn btn-primary"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                <form action="{{ route('pengguna.destroy', $pengguna) }}" method="POST" data-confirm="Hapus pengguna ini?">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger"><i class="icon-base bx bx-trash"></i></button>
                </form>
                <a href="{{ route('pengguna.index') }}" class="btn btn-outline-secondary"><i class="icon-base bx bx-arrow-back me-1"></i> Kembali</a>
            </div>
        </div>
        <div class="card mb-6">
            <div class="card-body">
                <div class="d-flex align-items-center gap-4 mb-6 pb-4 border-bottom">
                    <div class="avatar avatar-xl">
                        <span class="avatar-initial rounded-circle bg-label-primary" style="font-size:1.5rem;">
                            {{ strtoupper(substr($pengguna->name, 0, 1)) }}
                        </span>
                    </div>
                    <div>
                        <h5 class="mb-1">{{ $pengguna->name }}</h5>
                        <small class="text-muted">{{ $pengguna->email }}</small>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-sm-6"><small class="text-muted d-block mb-1">Nama</small><span class="fw-medium">{{ $pengguna->name }}</span></div>
                    <div class="col-sm-6"><small class="text-muted d-block mb-1">Email</small><span class="fw-medium">{{ $pengguna->email }}</span></div>
                    <div class="col-sm-6"><small class="text-muted d-block mb-1">Role</small><span class="fw-medium">{{ $pengguna->role?->name ?? '-' }}</span></div>
                    <div class="col-sm-6"><small class="text-muted d-block mb-1">Bergabung</small><span class="fw-medium">{{ $pengguna->created_at ? $pengguna->created_at->format('d M Y, H:i') : '-' }}</span></div>
                    <div class="col-sm-6"><small class="text-muted d-block mb-1">Diperbarui</small><span class="fw-medium">{{ $pengguna->updated_at ? $pengguna->updated_at->format('d M Y, H:i') : '-' }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
