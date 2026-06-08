@extends('layouts/contentNavbarLayout')
@section('title', 'Edit Versi')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Edit Versi</h5>
                <a href="{{ route('versi.index') }}" class="btn btn-outline-secondary btn-sm"><i class="icon-base bx bx-arrow-back me-1"></i> Kembali</a>
            </div>
            <div class="card-body">
                @if($errors->any())
                <div class="alert alert-danger alert-dismissible mb-6"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif
                <form action="{{ route('versi.update', $versi) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row g-5">
                        <div class="col-md-6">
                            <label class="form-label">Nama Versi <span class="text-danger">*</span></label>
                            <input type="text" name="version_name" class="form-control @error('version_name') is-invalid @enderror" value="{{ old('version_name', $versi->version_name) }}" placeholder="contoh: 1.0.0">
                            @error('version_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kode Versi <span class="text-danger">*</span></label>
                            <input type="number" name="version_code" class="form-control @error('version_code') is-invalid @enderror" value="{{ old('version_code', $versi->version_code) }}" placeholder="contoh: 1" min="1">
                            @error('version_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="d-flex gap-3 mt-5">
                        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i> Perbarui</button>
                        <a href="{{ route('versi.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
