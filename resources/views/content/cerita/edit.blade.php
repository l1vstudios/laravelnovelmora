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

const availableAds = @json($ads->map(fn($ad) => ['id' => $ad->id, 'title' => $ad->title, 'media_type' => $ad->media_type])->values());
const adPlacements = @json($cerita->adPlacements->groupBy('after_chapter')->map(fn($items) => $items->pluck('ad_id')->values())->toArray());

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function normalizeChapterContentText(value) {
    return String(value || '')
        .replace(/\r\n?/g, '\n')
        .replace(/\u00a0/g, ' ')
        .replace(/[‐‑‒–—―]+/gu, '-');
}

function normalizeChapterTitleText(value) {
    return normalizeChapterContentText(value).replace(/\s+/g, ' ').trim();
}

function normalizePastedChapterContentText(value) {
    const text = normalizeChapterContentText(value);

    if (/\n\s*\n/.test(text)) {
        return text;
    }

    const lines = text
        .split('\n')
        .map((line) => line.trim())
        .filter(Boolean);

    if (lines.length < 4) {
        return text;
    }

    const paragraphs = [];
    let current = [];
    let currentLength = 0;

    lines.forEach((line, index) => {
        const nextLine = lines[index + 1] || '';
        current.push(line);
        currentLength += line.length + 1;

        const endsSentence = /[.!?]["')\]]?$/.test(line);
        const nextStartsSentence = /^["'“‘]?[A-Z0-9]/.test(nextLine);
        const shouldBreak = index === lines.length - 1
            || (endsSentence && nextStartsSentence && currentLength >= 350);

        if (shouldBreak) {
            paragraphs.push(current.join(' '));
            current = [];
            currentLength = 0;
        }
    });

    return paragraphs.join('\n\n');
}

function normalizeChapterField(field) {
    field.value = field.matches('.chapter-title')
        ? normalizeChapterTitleText(field.value)
        : normalizeChapterContentText(field.value);
}

function pasteNormalizedText(field, text) {
    const normalized = field.matches('.chapter-title')
        ? normalizeChapterTitleText(text)
        : normalizePastedChapterContentText(text);
    const start = field.selectionStart;
    const end = field.selectionEnd;

    field.value = `${field.value.slice(0, start)}${normalized}${field.value.slice(end)}`;
    field.selectionStart = field.selectionEnd = start + normalized.length;
    field.dispatchEvent(new Event('input', { bubbles: true }));
}

function htmlToTextareaText(value) {
    const html = String(value || '');
    if (!/<[a-z][\s\S]*>/i.test(html)) return html;

    const wrapper = document.createElement('div');
    wrapper.innerHTML = html
        .replace(/<br\s*\/?>/gi, '\n')
        .replace(/<\/(p|div|h[1-6]|blockquote|li)>/gi, '\n\n');

    return wrapper.textContent.replace(/\n{3,}/g, '\n\n').trim();
}

function renderAdsOptions(chapterNumber, selectedAds = []) {
    if (!availableAds.length) {
        return '<small class="text-muted">Belum ada ads aktif.</small>';
    }

    const selected = selectedAds.map(String);

    return availableAds.map((ad) => {
        const checked = selected.includes(String(ad.id)) ? 'checked' : '';
        const label = `${escapeHtml(ad.title)} (${ad.media_type === 'video' ? 'Video' : 'Gambar'})`;

        return `
            <div class="form-check form-check-inline mb-2">
                <input class="form-check-input chapter-ad" type="checkbox"
                    name="ads_after_chapters[${chapterNumber}][]" value="${ad.id}"
                    id="ad_${chapterNumber}_${ad.id}" ${checked}>
                <label class="form-check-label" for="ad_${chapterNumber}_${ad.id}">${label}</label>
            </div>`;
    }).join('');
}

let chapterCount = 0;

function normalizeChapter(chapter, index) {
    if (chapter && typeof chapter === 'object') {
        return {
            title: chapter.title || `Chapter ${index + 1}`,
            content: chapter.content || '',
        };
    }

    return {
        title: `Chapter ${index + 1}`,
        content: chapter || '',
    };
}

function addChapter(title = '', content = '', locked = false, selectedAds = []) {
    chapterCount++;
    const container = document.getElementById('chapters-container');
    const div = document.createElement('div');
    div.className = 'card mb-4 chapter-row border';
    div.dataset.index = chapterCount;
    const adsMarkup = renderAdsOptions(chapterCount, selectedAds);
    const chapterContent = htmlToTextareaText(content);
    div.innerHTML = `
        <div class="card-header d-flex align-items-center justify-content-between py-3">
            <span class="fw-medium">Chapter ${chapterCount}</span>
            <div class="d-flex align-items-center gap-3">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input chapter-lock" type="checkbox" value="${chapterCount}"
                        name="locked_chapters[]" ${locked ? 'checked' : ''} id="lock_${chapterCount}">
                    <label class="form-check-label" for="lock_${chapterCount}">Locked</label>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeChapter(this)">
                    <i class="icon-base bx bx-trash"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="title_${chapterCount}" class="form-label">Judul Chapter</label>
                <input type="text" name="chapter_titles[]" id="title_${chapterCount}" class="form-control chapter-title"
                    placeholder="Tulis judul chapter ${chapterCount}..." value="${escapeHtml(title)}">
            </div>
            <textarea name="chapters[]" id="content_${chapterCount}" rows="5" class="form-control chapter-content"
                placeholder="Tulis isi chapter ${chapterCount}...">${escapeHtml(chapterContent)}</textarea>
            <div class="mt-4 pt-4 border-top">
                <label class="form-label mb-2">Sisipkan Ads setelah chapter ini</label>
                <div class="d-flex flex-wrap gap-3">${adsMarkup}</div>
            </div>
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
        row.querySelector('label[for^="lock_"]').setAttribute('for', `lock_${num}`);

        const titleInput = row.querySelector('.chapter-title');
        titleInput.placeholder = `Tulis judul chapter ${num}...`;
        titleInput.id = `title_${num}`;
        row.querySelector('label[for^="title_"]').setAttribute('for', `title_${num}`);

        row.querySelector('.chapter-content').placeholder = `Tulis isi chapter ${num}...`;

        row.querySelectorAll('.chapter-ad').forEach((adInput) => {
            adInput.name = `ads_after_chapters[${num}][]`;
            adInput.id = `ad_${num}_${adInput.value}`;
            const adLabel = row.querySelector(`label[for^="ad_"][for$="_${adInput.value}"]`);
            if (adLabel) adLabel.setAttribute('for', adInput.id);
        });
    });
    chapterCount = document.querySelectorAll('.chapter-row').length;
}

document.addEventListener('paste', function (event) {
    const field = event.target.closest('.chapter-title, .chapter-content');
    if (!field) return;

    event.preventDefault();
    pasteNormalizedText(field, event.clipboardData.getData('text'));
});

document.addEventListener('blur', function (event) {
    if (event.target.matches('.chapter-title, .chapter-content')) {
        normalizeChapterField(event.target);
    }
}, true);

document.addEventListener('submit', function (event) {
    if (!event.target.querySelector('.chapter-title, .chapter-content')) return;

    event.target.querySelectorAll('.chapter-title, .chapter-content').forEach(normalizeChapterField);
});

window.addEventListener('DOMContentLoaded', function () {
    const oldChapters = @json(old('chapters', null));

    if (Array.isArray(oldChapters)) {
        const oldTitles = @json(old('chapter_titles', []));
        const oldLocks = @json(old('locked_chapters', []));
        const oldAds = @json(old('ads_after_chapters', []));

        oldChapters.forEach((content, i) => {
            const chapterNumber = i + 1;
            const locked = oldLocks.map(String).includes(String(chapterNumber));
            addChapter(oldTitles[i] || `Chapter ${chapterNumber}`, content || '', locked, oldAds[chapterNumber] || []);
        });

        return;
    }

    const isiCerita = @json($cerita->isi_cerita ?? []);
    const lockData = @json($cerita->lock ?? []);

    Object.keys(isiCerita).forEach(function (key, i) {
        const chapterNumber = i + 1;
        const chapter = normalizeChapter(isiCerita[key], i);
        const isLocked = lockData[key] === true;
        addChapter(chapter.title, chapter.content, isLocked, adPlacements[chapterNumber] || []);
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
                            <label for="sinopsis" class="form-label">Sinopsis</label>
                            <textarea name="sinopsis" id="sinopsis" rows="3"
                                class="form-control @error('sinopsis') is-invalid @enderror"
                                placeholder="Tulis sinopsis singkat cerita...">{{ old('sinopsis', $cerita->sinopsis) }}</textarea>
                            @error('sinopsis')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
