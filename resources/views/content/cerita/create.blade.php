@extends('layouts/contentNavbarLayout')
@section('title', 'Tambah Cerita')
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

    function escapeHtml(value) {
      return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
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

    function addChapter(content = '', locked = false, selectedAds = []) {
      chapterCount++;
      const container = document.getElementById('chapters-container');
      const div = document.createElement('div');
      div.className = 'card mb-4 chapter-row border';
      div.dataset.index = chapterCount;
      const adsMarkup = renderAdsOptions(chapterCount, selectedAds);
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
            <textarea name="chapters[]" rows="5" class="form-control"
                placeholder="Tulis isi chapter ${chapterCount}...">${content}</textarea>
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
        row.querySelector('label').setAttribute('for', `lock_${num}`);
        row.querySelector('textarea').placeholder = `Tulis isi chapter ${num}...`;
        row.querySelectorAll('.chapter-ad').forEach((adInput) => {
          adInput.name = `ads_after_chapters[${num}][]`;
          adInput.id = `ad_${num}_${adInput.value}`;
          const adLabel = row.querySelector(`label[for^="ad_"][for$="_${adInput.value}"]`);
          if (adLabel) adLabel.setAttribute('for', adInput.id);
        });
      });
      chapterCount = document.querySelectorAll('.chapter-row').length;
    }
  </script>
@endsection
@section('content')
  <div class="row justify-content-center">
    <div class="col-md-10">
      <div class="card mb-6">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="mb-0">Tambah Cerita</h5>
          <a href="{{ route('cerita.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="icon-base bx bx-arrow-back me-1"></i> Kembali
          </a>
        </div>
        <div class="card-body">
          @if ($errors->any())
            <div class="alert alert-danger alert-dismissible mb-6" role="alert">
              <ul class="mb-0">
                @foreach ($errors->all() as $e)
                  <li>{{ $e }}</li>
                @endforeach
              </ul>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          @endif
          <form action="{{ route('cerita.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <h6 class="mb-4 text-muted text-uppercase" style="font-size:.75rem;letter-spacing:.08em;">Informasi Dasar</h6>
            <div class="row g-5 mb-6">
              <div class="col-md-8">
                <label class="form-label">Judul Cerita <span class="text-danger">*</span></label>
                <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror"
                  value="{{ old('judul') }}" placeholder="Masukkan judul cerita" autofocus>
                @error('judul')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Kategori</label>
                <select name="id_kategori" class="form-select">
                  <option value="">-- Pilih Kategori --</option>
                  @foreach ($kategoris as $kat)
                    <option value="{{ $kat->id }}" {{ old('id_kategori') == $kat->id ? 'selected' : '' }}>
                      {{ $kat->default_title }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="col-12">
                <label class="form-label">Cover Cerita</label>
                <div class="d-flex align-items-start gap-4">
                  <img id="cover-preview" src="" alt="Preview" class="rounded border object-fit-cover"
                    style="width:120px;height:160px;display:none;object-fit:cover;">
                  <div class="flex-fill">
                    <input type="file" name="cover" id="cover-input"
                      accept="image/jpg,image/jpeg,image/png,image/webp"
                      class="form-control @error('cover') is-invalid @enderror" onchange="previewCover(this)">
                    <small class="text-muted">JPG, PNG, atau WebP. Maks 2MB.</small>
                    @error('cover')
                      <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
              <div class="col-12">
                <h6 class="mb-3 text-muted text-uppercase" style="font-size:.75rem;letter-spacing:.08em;">Pengaturan</h6>
                <div class="row g-4">
                  <div class="col-md-4">
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" name="status" id="status"
                        {{ old('status', '1') ? 'checked' : '' }}>
                      <label class="form-check-label" for="status">Status Aktif</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" name="recomendation" id="recomendation"
                        {{ old('recomendation') ? 'checked' : '' }}>
                      <label class="form-check-label" for="recomendation">Rekomendasi</label>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" name="wajib_dibaca" id="wajib_dibaca"
                        {{ old('wajib_dibaca') ? 'checked' : '' }}>
                      <label class="form-check-label" for="wajib_dibaca">Wajib Dibaca</label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="d-flex align-items-center justify-content-between mb-4">
              <h6 class="mb-0 text-muted text-uppercase" style="font-size:.75rem;letter-spacing:.08em;">Isi Cerita
                (Chapters)</h6>
              <button type="button" class="btn btn-outline-primary btn-sm" onclick="addChapter()">
                <i class="icon-base bx bx-plus me-1"></i> Tambah Chapter
              </button>
            </div>
            <div id="chapters-container"></div>
            <div class="mt-2 mb-6 text-center text-muted" id="no-chapter-msg">
              <small>Klik "Tambah Chapter" untuk mulai menulis cerita.</small>
            </div>
            <div class="d-flex gap-3 mt-4">
              <button type="submit" class="btn btn-primary">
                <i class="icon-base bx bx-save me-1"></i> Simpan
              </button>
              <a href="{{ route('cerita.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <script>
    document.getElementById('chapters-container').addEventListener('DOMNodeInserted', function() {
      const msg = document.getElementById('no-chapter-msg');
      if (msg) msg.style.display = document.querySelectorAll('.chapter-row').length ? 'none' : 'block';
    });
    document.getElementById('chapters-container').addEventListener('DOMNodeRemoved', function() {
      setTimeout(() => {
        const msg = document.getElementById('no-chapter-msg');
        if (msg) msg.style.display = document.querySelectorAll('.chapter-row').length ? 'none' : 'block';
      }, 10);
    });
  </script>
@endsection
