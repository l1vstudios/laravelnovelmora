@extends('layouts/contentNavbarLayout')
@section('title', 'Edit Cerita')

@section('page-script')
<script>
function previewCover(input) {
    const preview = document.getElementById('cover-preview');
    if (input.files && input.files[0]) {
        preview.src = URL.createObjectURL(input.files[0]);
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}

let chapterCount = 0;

function addChapter(content = '', locked = false) {
    chapterCount++;
    const container = document.getElementById('chapters-container');
    const div = document.createElement('div');
    div.className = 'card mb-4 chapter-row border';
    div.innerHTML = `
        <div class="card-header d-flex align-items-center justify-content-between py-3">
            <span class="fw-medium">Chapter ${chapterCount}</span>
            <div class="d-flex align-items-center gap-3">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input chapter-lock" type="checkbox" value="${chapterCount}"
                        name="locked_chapters[]" ${locked ? 'checked' : ''} id="lock_${chapterCount}">
                    <label class="form-check-label" for="lock_${chapterCount}">Kunci</label>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeChapter(this)">
                    <i class="icon-base bx bx-trash"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <textarea name="chapters[]" rows="5" class="form-control"
                placeholder="Tulis isi chapter ${chapterCount}...">${content}</textarea>
        </div>`;
    container.appendChild(div);
    renumberChapters();
}

function removeChapter(btn) {
    btn.closest('.chapter-row').remove();
    renumberChapters();
}

function renumberChapters() {
    document.querySelectorAll('.chapter-row').forEach((row, i) => {
        const num = i + 1;
        row.querySelector('.fw-medium').textContent = `Chapter ${num}`;
        const lock = row.querySelector('.chapter-lock');
        lock.value = num;
        lock.id = `lock_${num}`;
        row.querySelector('label').setAttribute('for', `lock_${num}`);
    });
    chapterCount = document.querySelectorAll('.chapter-row').length;
}

// Load existing chapters on page load
window.addEventListener('DOMContentLoaded', function () {
    const isiCerita = @json($cerita->isi_cerita ?? []);
    const lockData  = @json($cerita->lock ?? []);
    Object.keys(isiCerita).forEach(function (key, i) {
        const isLocked = lockData[key] === true;
        addChapter(isiCerita[key], isLocked);
    });
});
</script>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">

        <div class="card mb-6">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Edit Cerita</h5>
                <a href="{{ route('cerita.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="icon-base bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
            <div class="card-body">

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible mb-6" role="alert">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <form action="{{ route('cerita.update', $cerita) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <h6 class="mb-4 text-muted text-uppercase" style="font-size:.75rem;letter-spacing:.08em;">Informasi Dasar</h6>
                    <div class="row g-5 mb-6">
                        <div class="col-md-8">
                            <label class="form-label">Judul Cerita <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror"
                                value="{{ old('judul', $cerita->judul) }}" placeholder="Masukkan judul cerita">
                            @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kategori</label>
                            <select name="id_kategori" class="form-select">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategoris as $kat)
                                    <option value="{{ $kat->id }}" {{ old('id_kategori', $cerita->id_kategori) == $kat->id ? 'selected' : '' }}>
                                        {{ $kat->default_title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Cover Cerita</label>
                            <div class="d-flex align-items-start gap-4">
                                <img id="cover-preview"
                                    src="{{ $cerita->cover ? asset('storage/' . $cerita->cover) : '' }}"
                                    alt="Cover"
                                    class="rounded border object-fit-cover"
                                    style="width:120px;height:160px;object-fit:cover;{{ $cerita->cover ? '' : 'display:none;' }}">
                                <div class="flex-fill">
                                    <input type="file" name="cover" id="cover-input" accept="image/jpg,image/jpeg,image/png,image/webp"
                                        class="form-control @error('cover') is-invalid @enderror"
                                        onchange="previewCover(this)">
                                    <small class="text-muted">
                                        {{ $cerita->cover ? 'Pilih file baru untuk mengganti cover.' : 'JPG, PNG, atau WebP. Maks 2MB.' }}
                                    </small>
                                    @error('cover')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <h6 class="mb-3 text-muted text-uppercase" style="font-size:.75rem;letter-spacing:.08em;">Pengaturan</h6>
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="status" id="status"
                                            {{ old('status', $cerita->status) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status">Status Aktif</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="recomendation" id="recomendation"
                                            {{ old('recomendation', $cerita->recomendation) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="recomendation">Rekomendasi</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="wajib_dibaca" id="wajib_dibaca"
                                            {{ old('wajib_dibaca', $cerita->wajib_dibaca) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="wajib_dibaca">Wajib Dibaca</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h6 class="mb-0 text-muted text-uppercase" style="font-size:.75rem;letter-spacing:.08em;">
                            Isi Cerita (Chapters)
                        </h6>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addChapter()">
                            <i class="icon-base bx bx-plus me-1"></i> Tambah Chapter
                        </button>
                    </div>

                    <div id="chapters-container"></div>

                    <div class="d-flex gap-3 mt-6">
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-base bx bx-save me-1"></i> Perbarui
                        </button>
                        <a href="{{ route('cerita.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>
@endsection
