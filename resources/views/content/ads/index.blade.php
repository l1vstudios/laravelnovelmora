@extends('layouts/contentNavbarLayout')
@section('title', 'Ads')

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const globalAdsModal = document.getElementById('global-ads-modal');
    const scopeAll = document.getElementById('global-ads-all');
    const storySelect = document.getElementById('global-ads-stories');
    const storySearch = document.getElementById('global-ads-story-search');
    const adSelect = document.getElementById('global-ads-list');
    const adSearch = document.getElementById('global-ads-search');
    const actionInputs = document.querySelectorAll('input[name="global_action"]');
    const submitButton = document.getElementById('global-ads-submit');
    const hasErrors = @json($errors->any());
    const oldFormType = @json(old('form_type'));

    function syncStoryPicker() {
        if (!scopeAll || !storySelect) return;

        storySelect.disabled = scopeAll.checked;
        storySelect.required = !scopeAll.checked;

        if (storySearch) {
            storySearch.disabled = scopeAll.checked;
            storySearch.value = scopeAll.checked ? '' : storySearch.value;
        }

        if (scopeAll.checked) {
            Array.from(storySelect.options).forEach((option) => {
                option.selected = false;
                option.hidden = false;
            });
        }
    }

    function filterSelect(selectInput, searchInput) {
        if (!selectInput || !searchInput) return;

        const needle = searchInput.value.trim().toLowerCase();

        Array.from(selectInput.options).forEach((option) => {
            option.hidden = needle && !option.textContent.toLowerCase().includes(needle);
        });
    }

    function syncSubmitButton() {
        if (!submitButton) return;

        const selectedAction = document.querySelector('input[name="global_action"]:checked')?.value || 'set';
        submitButton.innerHTML = selectedAction === 'remove'
            ? '<i class="icon-base bx bx-trash me-1"></i> Hapus Global'
            : '<i class="icon-base bx bx-world me-1"></i> Terapkan Global';
        submitButton.classList.toggle('btn-warning', selectedAction === 'remove');
        submitButton.classList.toggle('btn-primary', selectedAction !== 'remove');
    }

    if (scopeAll && storySelect) {
        scopeAll.addEventListener('change', syncStoryPicker);
        storySearch?.addEventListener('input', () => filterSelect(storySelect, storySearch));
        adSearch?.addEventListener('input', () => filterSelect(adSelect, adSearch));
        actionInputs.forEach((input) => input.addEventListener('change', syncSubmitButton));
        syncStoryPicker();
        filterSelect(storySelect, storySearch);
        filterSelect(adSelect, adSearch);
        syncSubmitButton();
    }

    if (hasErrors && oldFormType === 'global-ads' && globalAdsModal && window.bootstrap) {
        window.bootstrap.Modal.getOrCreateInstance(globalAdsModal).show();
    }
});
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible mb-6" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible mb-6" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                <h5 class="mb-0">Master Ads</h5>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#global-ads-modal">
                        <i class="icon-base bx bx-world me-1"></i> Set Ads Global
                    </button>
                    <a href="{{ route('ads.create') }}" class="btn btn-primary">
                        <i class="icon-base bx bx-plus me-1"></i> Tambah Ads
                    </a>
                </div>
            </div>
            <div class="card-body border-bottom pb-4 pt-3">
                <form method="GET" action="{{ route('ads.index') }}" class="row g-3 align-items-end">
                    <div class="col-auto">
                        <label class="form-label mb-1 text-muted" style="font-size:.75rem;">Tipe</label>
                        <select name="media_type" class="form-select form-select-sm" style="min-width:130px;">
                            <option value="">Semua</option>
                            <option value="image" {{ request('media_type') === 'image' ? 'selected' : '' }}>Gambar</option>
                            <option value="video" {{ request('media_type') === 'video' ? 'selected' : '' }}>Video</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label mb-1 text-muted" style="font-size:.75rem;">Status</label>
                        <select name="status" class="form-select form-select-sm" style="min-width:130px;">
                            <option value="">Semua</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-auto d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="icon-base bx bx-filter me-1"></i> Filter</button>
                        @if(request()->hasAny(['media_type','status']))
                        <a href="{{ route('ads.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Preview</th>
                            <th>Judul</th>
                            <th>Tipe</th>
                            <th>Status</th>
                            <th>Dipakai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($ads as $ad)
                        <tr>
                            <td>{{ $loop->iteration + ($ads->currentPage() - 1) * $ads->perPage() }}</td>
                            <td>
                                @if($ad->media_type === 'video')
                                    <video src="{{ $ad->media_src }}" muted style="width:90px;height:54px;object-fit:cover;" class="rounded bg-label-secondary"></video>
                                @else
                                    <img src="{{ $ad->media_src }}" alt="{{ $ad->title }}" class="rounded" style="width:90px;height:54px;object-fit:cover;" onerror="this.src='https://placehold.co/90x54'">
                                @endif
                            </td>
                            <td>
                                <span class="fw-medium">{{ $ad->title }}</span>
                                @if($ad->target_url)
                                <small class="text-muted d-block">{{ Str::limit($ad->target_url, 48) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($ad->media_type === 'video')
                                    <span class="badge bg-label-danger">Video</span>
                                @else
                                    <span class="badge bg-label-info">Gambar</span>
                                @endif
                            </td>
                            <td>
                                @if($ad->status)
                                    <span class="badge bg-label-success">Aktif</span>
                                @else
                                    <span class="badge bg-label-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td><span class="badge bg-label-primary">{{ $ad->placements_count }} posisi</span></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="icon-base bx bx-dots-vertical-rounded"></i></button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('ads.show', $ad) }}"><i class="icon-base bx bx-show me-1"></i> Detail</a>
                                        <a class="dropdown-item" href="{{ route('ads.edit', $ad) }}"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                                        <form action="{{ route('ads.destroy', $ad) }}" method="POST" data-confirm="Hapus ads ini?">
                                            @csrf @method('DELETE')
                                            <button class="dropdown-item text-danger"><i class="icon-base bx bx-trash me-1"></i> Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-6 text-muted">Belum ada ads. <a href="{{ route('ads.create') }}">Tambah sekarang</a>.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($ads->hasPages())<div class="card-footer d-flex justify-content-end">{{ $ads->links() }}</div>@endif
        </div>
    </div>
</div>

<div class="modal fade" id="global-ads-modal" tabindex="-1" aria-labelledby="global-ads-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="global-ads-modal-title">Set Ads Global</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                @if($globalAds->isEmpty() || $globalCeritas->isEmpty())
                    <div class="alert alert-info mb-0">Master ads atau cerita belum tersedia.</div>
                @else
                    <form method="POST" action="{{ route('ads.global-placements') }}" id="global-ads-form" class="row g-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="form_type" value="global-ads">
                        <input type="hidden" name="global_scope" value="selected">

                        <div class="col-md-6">
                            <label class="form-label d-block">Aksi</label>
                            <div class="btn-group" role="group" aria-label="Aksi ads global">
                                <input type="radio" class="btn-check" name="global_action" id="global-ads-action-set"
                                    value="set" {{ old('global_action', 'set') === 'set' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="global-ads-action-set">
                                    <i class="icon-base bx bx-world me-1"></i> Pasang
                                </label>

                                <input type="radio" class="btn-check" name="global_action" id="global-ads-action-remove"
                                    value="remove" {{ old('global_action') === 'remove' ? 'checked' : '' }}>
                                <label class="btn btn-outline-warning" for="global-ads-action-remove">
                                    <i class="icon-base bx bx-trash me-1"></i> Hapus
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="global-ads-position" class="form-label">Posisi</label>
                            <select name="placement_position" id="global-ads-position" class="form-select">
                                <option value="after" {{ old('placement_position', 'after') === 'after' ? 'selected' : '' }}>Setelah Chapter</option>
                                <option value="before" {{ old('placement_position') === 'before' ? 'selected' : '' }}>Sebelum Chapter</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="global-ads-list" class="form-label">Ads</label>
                            <input type="text" id="global-ads-search" class="form-control mb-2"
                                placeholder="Cari ads..." autocomplete="off">
                            <select id="global-ads-list" name="ad_ids[]" class="form-select" multiple size="7" required>
                                @foreach($globalAds as $globalAd)
                                    <option value="{{ $globalAd->id }}" {{ collect(old('ad_ids', []))->contains($globalAd->id) ? 'selected' : '' }}>
                                        {{ $globalAd->title }} ({{ $globalAd->media_type === 'video' ? 'Video' : 'Gambar' }}{{ $globalAd->status ? '' : ', Nonaktif' }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Tahan Ctrl atau Cmd untuk memilih lebih dari satu ads.</small>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="global_scope" value="all"
                                    id="global-ads-all" {{ old('global_scope') === 'all' ? 'checked' : '' }}>
                                <label class="form-check-label" for="global-ads-all">Pilih semua judul</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="global-ads-stories" class="form-label">Judul Cerita</label>
                            <input type="text" id="global-ads-story-search" class="form-control mb-2"
                                placeholder="Cari judul cerita..." autocomplete="off">
                            <select id="global-ads-stories" name="cerita_ids[]" class="form-select" multiple size="8">
                                @foreach($globalCeritas as $globalCerita)
                                    @php
                                        $chapterTotal = max((int) $globalCerita->parts, count($globalCerita->isi_cerita ?? []));
                                    @endphp
                                    <option value="{{ $globalCerita->id }}" {{ collect(old('cerita_ids', []))->contains($globalCerita->id) ? 'selected' : '' }}>
                                        {{ $globalCerita->judul }} ({{ $chapterTotal }} chapter)
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Tahan Ctrl atau Cmd untuk memilih lebih dari satu judul.</small>
                        </div>

                        <div class="col-md-6">
                            <label for="global-ads-start" class="form-label">Chapter Awal</label>
                            <input type="number" min="1" name="chapter_start" id="global-ads-start"
                                class="form-control" value="{{ old('chapter_start', 1) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label for="global-ads-end" class="form-label">Chapter Akhir</label>
                            <input type="number" min="1" name="chapter_end" id="global-ads-end"
                                class="form-control" value="{{ old('chapter_end', 1) }}" required>
                        </div>
                    </form>
                @endif
            </div>
            @if($globalAds->isNotEmpty() && $globalCeritas->isNotEmpty())
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="global-ads-form" id="global-ads-submit" class="btn btn-primary">
                        <i class="icon-base bx bx-world me-1"></i> Terapkan Global
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
