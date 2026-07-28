@extends('layouts/contentNavbarLayout')
@section('title', 'Edit Slider')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Edit Slider</h5>
                <a href="{{ route('slider.index') }}" class="btn btn-outline-secondary btn-sm"><i class="icon-base bx bx-arrow-back me-1"></i> Kembali</a>
            </div>
            <div class="card-body">
                @if($errors->any())
                <div class="alert alert-danger alert-dismissible mb-6"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif
                <form action="{{ route('slider.update', $slider) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="mb-5">
                        <label class="form-label">Upload Gambar Baru</label>
                        <input type="file" name="image_file" id="image_file" class="form-control @error('image_file') is-invalid @enderror"
                            accept="image/jpg,image/jpeg,image/png,image/webp">
                        @error('image_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Kosongkan jika tidak ingin mengganti gambar.</div>
                    </div>
                    <div class="mb-5">
                        <label class="form-label">Preview</label><br>
                        <img id="img-preview" src="{{ old('image_url', $slider->image_url) }}" alt="Preview" class="rounded" style="max-height:200px;" onerror="this.style.display='none'">
                    </div>
                    <div class="mb-5">
                        <label class="form-label">URL Gambar</label>
                        <input type="text" class="form-control" value="{{ $slider->image_url }}" readonly>
                    </div>
                    <div class="mb-5">
                        <label class="form-label">Lokasi File</label>
                        <input type="text" class="form-control" value="{{ $slider->image_path ?: '-' }}" readonly>
                    </div>
                    @include('content.slider._cerita-picker')
                    <div class="mb-5">
                        <div class="form-check form-switch">
                            <input type="hidden" name="status" value="0">
                            <input class="form-check-input" type="checkbox" name="status" id="status" value="1" {{ old('status', $slider->status) ? 'checked' : '' }}>
                            <label class="form-check-label" for="status">Status Aktif</label>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i> Perbarui</button>
                        <a href="{{ route('slider.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('image_file').addEventListener('change', function() {
    const file = this.files[0];
    const preview = document.getElementById('img-preview');

    if (file) {
        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
    }
});
</script>
@endsection
