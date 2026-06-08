@extends('layouts/contentNavbarLayout')
@section('title', 'Tambah Role')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Tambah Role</h5>
                <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="icon-base bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
            <div class="card-body">

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible mb-4" role="alert">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <form action="{{ route('roles.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label">Nama Role <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" placeholder="Contoh: Editor, Moderator" autofocus>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Deskripsi</label>
                        <input type="text" name="description" class="form-control"
                            value="{{ old('description') }}" placeholder="Opsional">
                    </div>

                    <div class="mb-5">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_super_admin" id="is_super_admin"
                                {{ old('is_super_admin') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_super_admin">
                                Super Admin
                                <small class="text-muted d-block">Dapat mengakses semua menu tanpa pembatasan.</small>
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-base bx bx-save me-1"></i> Simpan
                        </button>
                        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
