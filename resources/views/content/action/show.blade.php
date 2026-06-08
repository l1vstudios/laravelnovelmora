@extends('layouts/contentNavbarLayout')
@section('title', 'Detail Action')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Detail Action</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('action.edit', $action) }}" class="btn btn-primary btn-sm"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                    <form action="{{ route('action.destroy', $action) }}" method="POST" data-confirm="Hapus action ini?">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="icon-base bx bx-trash"></i></button>
                    </form>
                    <a href="{{ route('action.index') }}" class="btn btn-outline-secondary btn-sm"><i class="icon-base bx bx-arrow-back me-1"></i> Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-sm-6"><small class="text-muted d-block mb-1">Nama Action</small><span class="fw-medium">{{ $action->action_name }}</span></div>
                    <div class="col-sm-6"><small class="text-muted d-block mb-1">Dibuat</small><span class="fw-medium">{{ $action->created_at ? $action->created_at->format('d M Y') : '-' }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
