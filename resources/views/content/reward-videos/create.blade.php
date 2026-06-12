@extends('layouts/contentNavbarLayout')
@section('title', 'Tambah Reward Video')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Tambah Reward Video</h5>
                <a href="{{ route('reward-videos.index') }}" class="btn btn-outline-secondary btn-sm"><i class="icon-base bx bx-arrow-back me-1"></i> Kembali</a>
            </div>
            <div class="card-body">
                @if($errors->any())<div class="alert alert-danger alert-dismissible mb-6"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
                <form action="{{ route('reward-videos.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-5">
                        <label class="form-label">Judul Video <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Video Promo Senin" autofocus>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-5">
                        <label class="form-label">Upload Video</label>
                        <input type="file" name="video_file" id="video_file" class="form-control @error('video_file') is-invalid @enderror" accept="video/mp4,video/webm,video/quicktime">
                        @error('video_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">MP4, WebM, atau MOV. Maks 20MB.</small>
                    </div>
                    <div class="mb-5">
                        <label class="form-label">URL Video</label>
                        <input type="url" name="video_url" id="video_url" class="form-control @error('video_url') is-invalid @enderror" value="{{ old('video_url') }}" placeholder="https://...">
                        @error('video_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Kosongkan jika memakai upload video.</small>
                    </div>
                    <div id="preview-wrapper" class="mb-5" style="display:none;">
                        <label class="form-label">Preview</label>
                        <video id="video-preview" class="rounded w-100 bg-label-secondary" style="max-height:260px;object-fit:contain;" controls></video>
                    </div>
                    <div class="mb-5 form-check form-switch">
                        <input type="hidden" name="status" value="0">
                        <input class="form-check-input" type="checkbox" name="status" id="status" value="1" {{ old('status', '1') ? 'checked' : '' }}>
                        <label class="form-check-label" for="status">Status Aktif</label>
                    </div>
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i> Simpan</button>
                        <a href="{{ route('reward-videos.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
function updateVideoPreview() {
    const file = document.getElementById('video_file').files[0];
    const url = file ? URL.createObjectURL(file) : document.getElementById('video_url').value;
    const wrapper = document.getElementById('preview-wrapper');
    const preview = document.getElementById('video-preview');
    if (!url) { wrapper.style.display = 'none'; preview.removeAttribute('src'); return; }
    preview.src = url;
    wrapper.style.display = 'block';
}
document.getElementById('video_file').addEventListener('change', function() {
    if (this.files.length) document.getElementById('video_url').value = '';
    updateVideoPreview();
});
document.getElementById('video_url').addEventListener('input', updateVideoPreview);
updateVideoPreview();
</script>
@endsection
