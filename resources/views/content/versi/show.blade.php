@extends('layouts/contentNavbarLayout')
@section('title', 'Detail Versi')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Detail Versi</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('versi.edit', $versi) }}" class="btn btn-primary btn-sm"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                    <form action="{{ route('versi.destroy', $versi) }}" method="POST" data-confirm="Hapus versi ini?">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="icon-base bx bx-trash"></i></button>
                    </form>
                    <a href="{{ route('versi.index') }}" class="btn btn-outline-secondary btn-sm"><i class="icon-base bx bx-arrow-back me-1"></i> Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-sm-4"><small class="text-muted d-block mb-1">Nama Versi</small><span class="fw-medium">{{ $versi->version_name }}</span></div>
                    <div class="col-sm-4"><small class="text-muted d-block mb-1">Kode Versi</small><span class="badge bg-label-primary">{{ $versi->version_code }}</span></div>
                    <div class="col-sm-4"><small class="text-muted d-block mb-1">Dibuat</small><span class="fw-medium">{{ $versi->created_at ? $versi->created_at->format('d M Y') : '-' }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
