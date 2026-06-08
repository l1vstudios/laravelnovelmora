@extends('layouts/contentNavbarLayout')
@section('title', 'Detail Notifikasi')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="d-flex align-items-center justify-content-between mb-6">
            <h4 class="mb-0">Detail Notifikasi</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('notifikasi.edit', $notifikasi) }}" class="btn btn-primary"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                <form action="{{ route('notifikasi.destroy', $notifikasi) }}" method="POST" data-confirm="Hapus notifikasi ini?">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger"><i class="icon-base bx bx-trash"></i></button>
                </form>
                <a href="{{ route('notifikasi.index') }}" class="btn btn-outline-secondary"><i class="icon-base bx bx-arrow-back me-1"></i> Kembali</a>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h6 class="mb-0">{{ $notifikasi->title }}</h6></div>
            <div class="card-body">
                <p class="mb-4" style="white-space: pre-line;">{{ $notifikasi->message }}</p>
                <small class="text-muted">Dibuat: {{ $notifikasi->created_at ? $notifikasi->created_at->format('d M Y, H:i') : '-' }}</small>
            </div>
        </div>
    </div>
</div>
@endsection
