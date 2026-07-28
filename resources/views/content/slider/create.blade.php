@extends('layouts/contentNavbarLayout')
@section('title', 'Tambah Slider')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Tambah Slider</h5>
                <a href="{{ route('slider.index') }}" class="btn btn-outline-secondary btn-sm"><i class="icon-base bx bx-arrow-back me-1"></i> Kembali</a>
            </div>
            <div class="card-body">
                @if($errors->any())
                <div class="alert alert-danger alert-dismissible mb-6"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif
                <form action="{{ route('slider.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-5">
                        <label class="form-label">Upload Gambar <span class="text-danger">*</span></label>
                        <input type="file" name="image_file" class="form-control @error('image_file') is-invalid @enderror"
                            accept="image/jpg,image/jpeg,image/png,image/webp" autofocus>
                        @error('image_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">JPG, PNG, atau WebP. Maks 5MB. URL akan otomatis dibuat lengkap dengan domain.</div>
                    </div>
                    <div id="preview-wrapper" class="mb-5" style="display:none;">
                        <label class="form-label">Preview</label>
                        <img id="img-preview" src="" alt="Preview" class="rounded img-fluid" style="max-height:200px;">
                    </div>
                    @include('content.slider._cerita-picker')
                    <div class="mb-5">
                        <div class="form-check form-switch">
                            <input type="hidden" name="status" value="0">
                            <input class="form-check-input" type="checkbox" name="status" id="status" value="1" {{ old('status', '1') ? 'checked' : '' }}>
                            <label class="form-check-label" for="status">Status Aktif</label>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i> Simpan</button>
                        <a href="{{ route('slider.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.querySelector('[name="image_file"]').addEventListener('change', function() {
    const file = this.files[0];
    const preview = document.getElementById('img-preview');
    const wrapper = document.getElementById('preview-wrapper');

    if (file) { preview.src = URL.createObjectURL(file); wrapper.style.display = 'block'; }
    else { wrapper.style.display = 'none'; }
});
</script>
@endsection
