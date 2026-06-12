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
                <form action="{{ route('slider.update', $slider) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-5">
                        <label class="form-label">URL Gambar <span class="text-danger">*</span></label>
                        <input type="url" name="image_url" id="image_url" class="form-control @error('image_url') is-invalid @enderror"
                            value="{{ old('image_url', $slider->image_url) }}" placeholder="https://...">
                        @error('image_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-5">
                        <label class="form-label">Preview</label><br>
                        <img id="img-preview" src="{{ old('image_url', $slider->image_url) }}" alt="Preview" class="rounded" style="max-height:200px;" onerror="this.style.display='none'">
                    </div>
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
document.getElementById('image_url').addEventListener('input', function() {
    const preview = document.getElementById('img-preview');
    preview.src = this.value;
    preview.style.display = 'block';
});
</script>
@endsection
