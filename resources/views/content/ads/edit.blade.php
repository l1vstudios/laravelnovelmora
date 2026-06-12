@extends('layouts/contentNavbarLayout')
@section('title', 'Edit Ads')

@section('content')
@php
    $selectedPlacements = $ad->placements->groupBy('cerita_id')->map(fn($items) => $items->pluck('after_chapter')->values()->all())->toArray();
@endphp
<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Edit Ads</h5>
                <a href="{{ route('ads.index') }}" class="btn btn-outline-secondary btn-sm"><i class="icon-base bx bx-arrow-back me-1"></i> Kembali</a>
            </div>
            <div class="card-body">
                @if($errors->any())
                <div class="alert alert-danger alert-dismissible mb-6"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif
                <form action="{{ route('ads.update', $ad) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="row g-5">
                        <div class="col-md-8">
                            <label class="form-label">Judul Ads <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $ad->title) }}" placeholder="Contoh: Promo Paket Premium" autofocus>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipe Media <span class="text-danger">*</span></label>
                            <select name="media_type" id="media_type" class="form-select @error('media_type') is-invalid @enderror">
                                <option value="image" {{ old('media_type', $ad->media_type) === 'image' ? 'selected' : '' }}>Gambar</option>
                                <option value="video" {{ old('media_type', $ad->media_type) === 'video' ? 'selected' : '' }}>Video</option>
                            </select>
                            @error('media_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Upload File Baru</label>
                            <input type="file" name="media_file" id="media_file" class="form-control @error('media_file') is-invalid @enderror" accept="image/jpg,image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime">
                            @error('media_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Kosongkan jika tidak ingin mengganti file. Gambar maks 5MB, video maks 20MB.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">URL Media</label>
                            <input type="url" name="media_url" id="media_url" class="form-control @error('media_url') is-invalid @enderror" value="{{ old('media_url', $ad->media_path ? '' : $ad->media_url) }}" placeholder="https://...">
                            @error('media_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Isi URL untuk mengganti media menjadi link external.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">URL Direct Link</label>
                            <input type="url" name="target_url" class="form-control @error('target_url') is-invalid @enderror" value="{{ old('target_url', $ad->target_url) }}" placeholder="https://...">
                            @error('target_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Preview</label>
                            <div id="preview-media"></div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="status" value="0">
                                <input class="form-check-input" type="checkbox" name="status" id="status" value="1" {{ old('status', $ad->status) ? 'checked' : '' }}>
                                <label class="form-check-label" for="status">Status Aktif</label>
                            </div>
                        </div>
                        @include('content.ads._placements', ['ceritas' => $ceritas, 'selectedPlacements' => $selectedPlacements])
                    </div>
                    <div class="d-flex gap-3 mt-6">
                        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i> Perbarui</button>
                        <a href="{{ route('ads.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
const currentMediaSrc = @json($ad->media_src);

function updateAdsPreview() {
    const file = document.getElementById('media_file').files[0];
    const urlInput = document.getElementById('media_url');
    const url = file ? URL.createObjectURL(file) : (urlInput.value || currentMediaSrc);
    const type = document.getElementById('media_type').value;
    const media = document.getElementById('preview-media');

    media.innerHTML = type === 'video'
        ? `<video src="${url}" class="rounded w-100" style="max-height:260px;object-fit:contain;" controls></video>`
        : `<img src="${url}" alt="Preview" class="rounded img-fluid" style="max-height:260px;" onerror="this.style.display='none'">`;
}

document.getElementById('media_file').addEventListener('change', function() {
    if (this.files.length) document.getElementById('media_url').value = '';
    updateAdsPreview();
});
document.getElementById('media_url').addEventListener('input', updateAdsPreview);
document.getElementById('media_type').addEventListener('change', updateAdsPreview);
updateAdsPreview();
</script>
@endsection
