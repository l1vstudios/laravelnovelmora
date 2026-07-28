@extends('layouts/contentNavbarLayout')
@section('title', 'Edit Syarat Ketentuan')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Edit Syarat Ketentuan</h5>
                <a href="{{ route('syarat-ketentuan.index') }}" class="btn btn-outline-secondary btn-sm"><i class="icon-base bx bx-arrow-back me-1"></i> Kembali</a>
            </div>
            <div class="card-body">
                @if($errors->any())
                <div class="alert alert-danger alert-dismissible mb-6"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif
                <form action="{{ route('syarat-ketentuan.update', $syaratKetentuan) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-5">
                        <label class="form-label">Konten <span class="text-danger">*</span></label>
                        <textarea name="konten" rows="12" class="form-control @error('konten') is-invalid @enderror">{{ old('konten', $syaratKetentuan->konten) }}</textarea>
                        @error('konten')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i> Perbarui</button>
                        <a href="{{ route('syarat-ketentuan.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
