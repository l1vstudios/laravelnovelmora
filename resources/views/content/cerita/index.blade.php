@extends('layouts/contentNavbarLayout')
@section('title', 'Daftar Cerita')

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const scopeAll = document.getElementById('global-lock-all');
    const storyList = document.getElementById('global-lock-stories');
    const storySearch = document.getElementById('global-lock-story-search');
    const storySearchButton = document.getElementById('global-lock-story-search-button');
    const storyResetButton = document.getElementById('global-lock-story-reset-button');
    const storyPager = document.getElementById('global-lock-story-pager');
    const storyHidden = document.getElementById('global-lock-selected-stories');
    const storyCount = document.getElementById('global-lock-story-count');
    const actionInputs = document.querySelectorAll('input[name="lock_action"]');
    const submitButton = document.getElementById('global-lock-submit');
    const globalLockModal = document.getElementById('global-lock-modal');
    const bulkScopeAll = document.getElementById('bulk-pilihan-all');
    const bulkStoryList = document.getElementById('bulk-pilihan-stories');
    const bulkStorySearch = document.getElementById('bulk-pilihan-story-search');
    const bulkStorySearchButton = document.getElementById('bulk-pilihan-story-search-button');
    const bulkStoryResetButton = document.getElementById('bulk-pilihan-story-reset-button');
    const bulkStoryPager = document.getElementById('bulk-pilihan-story-pager');
    const bulkStoryHidden = document.getElementById('bulk-pilihan-selected-stories');
    const bulkStoryCount = document.getElementById('bulk-pilihan-story-count');
    const bulkFieldInputs = document.querySelectorAll('input[name="bulk_fields[]"]');
    const bulkActionInputs = document.querySelectorAll('input[name="bulk_action"]');
    const bulkSubmitButton = document.getElementById('bulk-pilihan-submit');
    const bulkPilihanModal = document.getElementById('bulk-pilihan-modal');
    const categoryDropdown = document.getElementById('cerita-category-dropdown');
    const categoryToggle = document.getElementById('cerita-category-toggle');
    const categoryInput = document.getElementById('cerita-category-input');
    const categorySearch = document.getElementById('cerita-category-search');
    const categoryLabel = document.getElementById('cerita-category-label');
    const categoryItems = document.querySelectorAll('[data-category-option]');
    const hasErrors = @json($errors->any());
    const oldFormType = @json(old('form_type'));
    const ceritaOptionsUrl = @json(route('cerita.index'));

    function syncActionButton() {
        const selectedAction = document.querySelector('input[name="lock_action"]:checked')?.value || 'lock';

        if (!submitButton) {
            return;
        }

        submitButton.innerHTML = selectedAction === 'unlock'
            ? '<i class="icon-base bx bx-lock-open me-1"></i> Unlock'
            : '<i class="icon-base bx bx-lock me-1"></i> Lock';
        submitButton.classList.toggle('btn-warning', selectedAction === 'unlock');
        submitButton.classList.toggle('btn-primary', selectedAction !== 'unlock');
    }

    function syncBulkButton() {
        const selectedFields = Array.from(document.querySelectorAll('input[name="bulk_fields[]"]:checked'))
            .map((input) => input.value);
        const selectedAction = document.querySelector('input[name="bulk_action"]:checked')?.value || 'enable';

        if (!bulkSubmitButton) {
            return;
        }

        const fieldLabel = selectedFields.length === 2
            ? 'Rekomendasi & Wajib Dibaca'
            : (selectedFields[0] === 'wajib_dibaca' ? 'Wajib Dibaca' : 'Rekomendasi');
        const actionLabel = selectedAction === 'disable' ? 'Nonaktifkan' : 'Aktifkan';

        bulkSubmitButton.innerHTML = selectedAction === 'disable'
            ? `<i class="icon-base bx bx-x-circle me-1"></i> ${actionLabel} ${fieldLabel}`
            : `<i class="icon-base bx bx-check-circle me-1"></i> ${actionLabel} ${fieldLabel}`;
        bulkSubmitButton.classList.toggle('btn-warning', selectedAction === 'disable');
        bulkSubmitButton.classList.toggle('btn-primary', selectedAction !== 'disable');
    }

    function storyOptionLabel(story, includeParts) {
        const title = story.judul || story.title || '';
        const parts = Number(story.parts || 0);

        return includeParts ? `${title} (${parts} chapter)` : title;
    }

    function pickerState(listInput) {
        if (listInput._storyPickerState) {
            return listInput._storyPickerState;
        }

        const selected = new Map();

        listInput.querySelectorAll('[data-story-checkbox]:checked').forEach((checkbox) => {
            selected.set(String(checkbox.value), {
                id: checkbox.value,
                judul: checkbox.dataset.judul || '',
                parts: checkbox.dataset.parts || 0,
            });
        });

        listInput._storyPickerState = {
            selected,
            currentPage: Number(listInput.dataset.currentPage || 1),
            lastPage: Number(listInput.dataset.lastPage || 1),
            loaded: listInput.children.length > 0,
        };

        return listInput._storyPickerState;
    }

    function syncHiddenStories(state, hiddenInput, countInput) {
        hiddenInput.innerHTML = '';

        state.selected.forEach((story) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'cerita_ids[]';
            input.value = story.id;
            hiddenInput.appendChild(input);
        });

        if (countInput) {
            countInput.textContent = `${state.selected.size} dipilih`;
        }
    }

    function renderStoryRows(listInput, stories, includeParts) {
        const state = pickerState(listInput);
        listInput.innerHTML = '';

        if (!stories.length) {
            const empty = document.createElement('div');
            empty.className = 'list-group-item text-muted text-center py-4';
            empty.textContent = 'Tidak ada cerita ditemukan.';
            listInput.appendChild(empty);
            return;
        }

        stories.forEach((story) => {
            const id = String(story.id);
            const label = document.createElement('label');
            label.className = 'list-group-item d-flex align-items-center gap-3';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'form-check-input m-0';
            checkbox.value = id;
            checkbox.checked = state.selected.has(id);
            checkbox.dataset.storyCheckbox = '1';
            checkbox.dataset.judul = story.judul || story.title || '';
            checkbox.dataset.parts = story.parts || 0;

            const text = document.createElement('span');
            text.className = 'flex-grow-1';
            text.textContent = storyOptionLabel(story, includeParts);

            label.appendChild(checkbox);
            label.appendChild(text);
            listInput.appendChild(label);
        });
    }

    function renderStoryMessage(listInput, message, className = 'text-muted') {
        listInput.innerHTML = '';

        const item = document.createElement('div');
        item.className = `list-group-item text-center py-4 ${className}`;
        item.textContent = message;
        listInput.appendChild(item);
    }

    function showLoadError(listInput, error) {
        const detail = error?.message ? ` (${error.message})` : '';
        renderStoryMessage(listInput, `Gagal memuat cerita${detail}.`, 'text-danger');
    }

    function renderStoryPager(pagerInput, state) {
        if (!pagerInput) {
            return;
        }

        pagerInput.querySelector('[data-story-prev]').disabled = state.currentPage <= 1;
        pagerInput.querySelector('[data-story-next]').disabled = state.currentPage >= state.lastPage;
        pagerInput.querySelector('[data-story-page-label]').disabled = true;
        pagerInput.querySelector('[data-story-page-label]').textContent = `Halaman ${state.currentPage} / ${state.lastPage}`;
    }

    function setupPaginatedStoryPicker(scopeAllInput, listInput, searchInput, searchButton, resetButton, pagerInput, hiddenInput, countInput, modalInput, includeParts) {
        if (!scopeAllInput || !listInput || !hiddenInput) {
            return;
        }

        const state = pickerState(listInput);
        let timer = null;

        async function loadOptions(page = 1) {
            if (scopeAllInput.checked) {
                return;
            }

            state.currentPage = page;
            renderStoryPager(pagerInput, state);
            renderStoryMessage(listInput, 'Memuat cerita...');

            const params = new URLSearchParams({ story_options: '1', page: String(page), per_page: '5' });
            const keyword = searchInput?.value.trim() || '';

            if (keyword) {
                params.set('q', keyword);
            }

            const response = await fetch(`${ceritaOptionsUrl}?${params.toString()}`, {
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const contentType = response.headers.get('content-type') || '';

            if (!contentType.includes('application/json')) {
                throw new Error('response bukan JSON');
            }

            const payload = await response.json();
            const meta = payload.meta || {};

            state.currentPage = Number(meta.current_page || page);
            state.lastPage = Number(meta.last_page || 1);
            state.loaded = true;

            renderStoryRows(listInput, payload.data || [], includeParts);
            renderStoryPager(pagerInput, state);
            syncHiddenStories(state, hiddenInput, countInput);
        }

        function syncScope() {
            const isAll = scopeAllInput.checked;
            listInput.classList.toggle('opacity-50', isAll);
            listInput.querySelectorAll('[data-story-checkbox]').forEach((checkbox) => {
                checkbox.disabled = isAll;
                checkbox.checked = false;
            });

            if (searchInput) {
                searchInput.disabled = isAll;
                searchInput.value = isAll ? '' : searchInput.value;
            }

            if (searchButton) {
                searchButton.disabled = isAll;
            }

            if (resetButton) {
                resetButton.disabled = isAll;
            }

            if (pagerInput) {
                renderStoryPager(pagerInput, state);
                pagerInput.querySelectorAll('button').forEach((button) => {
                    button.disabled = isAll || button.disabled;
                });
            }

            if (isAll) {
                state.selected.clear();
                hiddenInput.innerHTML = '';
                syncHiddenStories(state, hiddenInput, countInput);
            }
        }

        listInput.addEventListener('change', (event) => {
            const checkbox = event.target.closest('[data-story-checkbox]');

            if (!checkbox) {
                return;
            }

            const id = String(checkbox.value);

            if (checkbox.checked) {
                state.selected.set(id, {
                    id,
                    judul: checkbox.dataset.judul || '',
                    parts: checkbox.dataset.parts || 0,
                });
            } else {
                state.selected.delete(id);
            }

            syncHiddenStories(state, hiddenInput, countInput);
        });

        scopeAllInput.addEventListener('change', syncScope);

        if (searchInput) {
            searchInput.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter') {
                    return;
                }

                event.preventDefault();
                loadOptions(1).catch((error) => showLoadError(listInput, error));
            });

            searchInput.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    loadOptions(1).catch((error) => showLoadError(listInput, error));
                }, 250);
            });
        }

        searchButton?.addEventListener('click', () => {
            loadOptions(1).catch((error) => showLoadError(listInput, error));
        });

        resetButton?.addEventListener('click', () => {
            if (searchInput) {
                searchInput.value = '';
            }

            loadOptions(1).catch((error) => showLoadError(listInput, error));
        });

        pagerInput?.addEventListener('click', (event) => {
            const prevButton = event.target.closest('[data-story-prev]');
            const nextButton = event.target.closest('[data-story-next]');

            if (!prevButton && !nextButton) {
                return;
            }

            event.preventDefault();

            if (prevButton?.disabled || nextButton?.disabled) {
                return;
            }

            const nextPage = prevButton
                ? Math.max(1, state.currentPage - 1)
                : Math.min(state.lastPage, state.currentPage + 1);

            loadOptions(nextPage).catch((error) => showLoadError(listInput, error));
        });

        modalInput?.addEventListener('shown.bs.modal', () => {
            if (!state.loaded) {
                loadOptions(1).catch((error) => showLoadError(listInput, error));
            }
        });

        syncHiddenStories(state, hiddenInput, countInput);
        renderStoryPager(pagerInput, state);
        syncScope();
    }

    function filterCategoryOptions() {
        if (!categorySearch) {
            return;
        }

        const needle = categorySearch.value.trim().toLowerCase();

        categoryItems.forEach((item) => {
            const label = item.dataset.categoryLabel || '';
            item.classList.toggle('d-none', needle && !label.includes(needle));
        });
    }

    if (scopeAll && storyList) {
        setupPaginatedStoryPicker(scopeAll, storyList, storySearch, storySearchButton, storyResetButton, storyPager, storyHidden, storyCount, globalLockModal, true);
        actionInputs.forEach((input) => input.addEventListener('change', syncActionButton));
        syncActionButton();
    }

    if (bulkScopeAll && bulkStoryList) {
        setupPaginatedStoryPicker(bulkScopeAll, bulkStoryList, bulkStorySearch, bulkStorySearchButton, bulkStoryResetButton, bulkStoryPager, bulkStoryHidden, bulkStoryCount, bulkPilihanModal, false);
        bulkFieldInputs.forEach((input) => input.addEventListener('change', syncBulkButton));
        bulkActionInputs.forEach((input) => input.addEventListener('change', syncBulkButton));
        syncBulkButton();
    }

    if (categoryDropdown && categoryInput && categoryLabel) {
        categoryItems.forEach((item) => {
            item.addEventListener('click', () => {
                categoryInput.value = item.dataset.categoryValue || '';
                categoryLabel.textContent = item.dataset.categoryText || 'Semua Kategori';
                categoryItems.forEach((option) => option.classList.remove('active'));
                item.classList.toggle('active', Boolean(item.dataset.categoryValue));

                if (categoryToggle && window.bootstrap) {
                    window.bootstrap.Dropdown.getOrCreateInstance(categoryToggle).hide();
                }
            });
        });

        if (categorySearch) {
            categorySearch.addEventListener('click', (event) => event.stopPropagation());
            categorySearch.addEventListener('input', filterCategoryOptions);
            categoryToggle?.addEventListener('shown.bs.dropdown', () => {
                categorySearch.focus();
                categorySearch.select();
                filterCategoryOptions();
            });
            categoryToggle?.addEventListener('hidden.bs.dropdown', () => {
                categorySearch.value = '';
                filterCategoryOptions();
            });
        }
    }

    if (hasErrors && oldFormType === 'global-lock' && globalLockModal && window.bootstrap) {
        window.bootstrap.Modal.getOrCreateInstance(globalLockModal).show();
    }

    if (hasErrors && oldFormType === 'bulk-pilihan' && bulkPilihanModal && window.bootstrap) {
        window.bootstrap.Modal.getOrCreateInstance(bulkPilihanModal).show();
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
                <h5 class="mb-0">Daftar Cerita</h5>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#bulk-pilihan-modal">
                        <i class="icon-base bx bx-check-square me-1"></i> Set Flag Cerita
                    </button>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#global-lock-modal">
                        <i class="icon-base bx bx-lock me-1"></i> Set Lock Global
                    </button>
                    <a href="{{ route('cerita.create') }}" class="btn btn-primary">
                        <i class="icon-base bx bx-plus me-1"></i> Tambah Cerita
                    </a>
                </div>
            </div>
            <div class="card-body border-bottom pb-4 pt-3">
                <form method="GET" action="{{ route('cerita.index') }}" class="row g-3 align-items-end">
                    <div class="col-auto">
                        <label class="form-label mb-1 text-muted" style="font-size:.75rem;">Status</label>
                        <select name="status" class="form-select form-select-sm" style="min-width:130px;">
                            <option value="">Semua</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label mb-1 text-muted" style="font-size:.75rem;">Rekomendasi</label>
                        <select name="recomendation" class="form-select form-select-sm" style="min-width:130px;">
                            <option value="">Semua</option>
                            <option value="1" {{ request('recomendation') === '1' ? 'selected' : '' }}>Ya</option>
                            <option value="0" {{ request('recomendation') === '0' ? 'selected' : '' }}>Tidak</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label mb-1 text-muted" style="font-size:.75rem;">Wajib Dibaca</label>
                        <select name="wajib_dibaca" class="form-select form-select-sm" style="min-width:130px;">
                            <option value="">Semua</option>
                            <option value="1" {{ request('wajib_dibaca') === '1' ? 'selected' : '' }}>Ya</option>
                            <option value="0" {{ request('wajib_dibaca') === '0' ? 'selected' : '' }}>Tidak</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        @php
                            $selectedKategori = $kategoris->firstWhere('id', (int) request('kategori_id'));
                        @endphp
                        <label class="form-label mb-1 text-muted" style="font-size:.75rem;">Kategori</label>
                        <input type="hidden" name="kategori_id" id="cerita-category-input" value="{{ request('kategori_id') }}">
                        <div class="dropdown" id="cerita-category-dropdown">
                            <button type="button" id="cerita-category-toggle" class="btn btn-sm btn-outline-secondary dropdown-toggle text-start d-flex align-items-center justify-content-between"
                                data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="min-width:210px;">
                                <span id="cerita-category-label">{{ $selectedKategori?->default_title ?? 'Semua Kategori' }}</span>
                            </button>
                            <div class="dropdown-menu p-2" style="min-width:260px;max-height:320px;overflow:auto;">
                                <input type="search" id="cerita-category-search" class="form-control form-control-sm mb-2"
                                    placeholder="Cari kategori..." autocomplete="off">
                                <button type="button" class="dropdown-item rounded"
                                    data-category-option data-category-value="" data-category-text="Semua Kategori" data-category-label="semua kategori">
                                    Semua Kategori
                                </button>
                                @foreach($kategoris as $kategori)
                                    <button type="button" class="dropdown-item rounded {{ (string) request('kategori_id') === (string) $kategori->id ? 'active' : '' }}"
                                        data-category-option
                                        data-category-value="{{ $kategori->id }}"
                                        data-category-text="{{ $kategori->default_title }}"
                                        data-category-label="{{ Str::lower($kategori->default_title) }}">
                                        {{ $kategori->default_title }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <label class="form-label mb-1 text-muted" style="font-size:.75rem;">Judul Cerita</label>
                        <input type="search" name="judul" class="form-control form-control-sm"
                            value="{{ request('judul') }}" placeholder="Cari judul..." style="min-width:220px;">
                    </div>
                    <div class="col-auto d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="icon-base bx bx-filter me-1"></i> Filter</button>
                        @if(request()->hasAny(['status','recomendation','wajib_dibaca','kategori_id','judul']))
                        <a href="{{ route('cerita.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cover</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Index</th>
                            <th>Parts</th>
                            <th>Read</th>
                            <th>Vote</th>
                            <th>Status</th>
                            <th>Rekomendasi</th>
                            <th>Wajib Baca</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($ceritas as $cerita)
                        <tr>
                            <td>{{ $loop->iteration + ($ceritas->currentPage() - 1) * $ceritas->perPage() }}</td>
                            <td>
                                @if($cerita->cover)
                                    <img src="{{ asset('storage/' . $cerita->cover) }}" alt="{{ $cerita->judul }}"
                                        class="rounded" style="width:40px;height:54px;object-fit:cover;">
                                @else
                                    <div class="rounded bg-label-secondary d-flex align-items-center justify-content-center"
                                        style="width:40px;height:54px;">
                                        <i class="icon-base bx bx-image text-muted" style="font-size:1.1rem;"></i>
                                    </div>
                                @endif
                            </td>
                            <td><span class="fw-medium">{{ $cerita->judul }}</span></td>
                            <td>{{ $cerita->kategori->default_title ?? '-' }}</td>
                            <td>{{ $cerita->positions_index ?? 0 }}</td>
                            <td><span class="badge bg-label-info">{{ $cerita->parts }} chapter</span></td>
                            <td>{{ number_format($cerita->total_read) }}</td>
                            <td>{{ number_format($cerita->total_vote) }}</td>
                            <td>
                                @if($cerita->status)
                                    <span class="badge bg-label-success">Aktif</span>
                                @else
                                    <span class="badge bg-label-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                @if($cerita->recomendation)
                                    <i class="icon-base bx bx-check-circle text-success"></i>
                                @else
                                    <i class="icon-base bx bx-x-circle text-secondary"></i>
                                @endif
                            </td>
                            <td>
                                @if($cerita->wajib_dibaca)
                                    <i class="icon-base bx bx-check-circle text-success"></i>
                                @else
                                    <i class="icon-base bx bx-x-circle text-secondary"></i>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="icon-base bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('cerita.show', $cerita) }}">
                                            <i class="icon-base bx bx-show me-1"></i> Detail
                                        </a>
                                        <a class="dropdown-item" href="{{ route('cerita.edit', $cerita) }}">
                                            <i class="icon-base bx bx-edit-alt me-1"></i> Edit
                                        </a>
                                        <form action="{{ route('cerita.destroy', $cerita) }}" method="POST"
                                            data-confirm="Hapus cerita ini?">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="icon-base bx bx-trash me-1"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-6 text-muted">
                                Belum ada cerita. <a href="{{ route('cerita.create') }}">Tambah sekarang</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($ceritas->hasPages())
            <div class="card-footer d-flex justify-content-end">
                {{ $ceritas->links() }}
            </div>
            @endif
        </div>

    </div>
</div>

<div class="modal fade" id="global-lock-modal" tabindex="-1" aria-labelledby="global-lock-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="global-lock-modal-title">Set Lock Global</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                @if(! $hasCeritas)
                    <div class="alert alert-info mb-0">Belum ada cerita untuk di-lock.</div>
                @else
                    <form method="POST" action="{{ route('cerita.global-lock') }}" id="global-lock-form" class="row g-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="form_type" value="global-lock">
                        <input type="hidden" name="lock_scope" value="selected">

                        <div class="col-12">
                            <label class="form-label d-block">Aksi</label>
                            <div class="btn-group" role="group" aria-label="Aksi lock chapter">
                                <input type="radio" class="btn-check" name="lock_action" id="global-lock-action-lock"
                                    value="lock" {{ old('lock_action', 'lock') === 'lock' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="global-lock-action-lock">
                                    <i class="icon-base bx bx-lock me-1"></i> Lock
                                </label>

                                <input type="radio" class="btn-check" name="lock_action" id="global-lock-action-unlock"
                                    value="unlock" {{ old('lock_action') === 'unlock' ? 'checked' : '' }}>
                                <label class="btn btn-outline-warning" for="global-lock-action-unlock">
                                    <i class="icon-base bx bx-lock-open me-1"></i> Unlock
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="lock_scope" value="all"
                                    id="global-lock-all" {{ old('lock_scope') === 'all' ? 'checked' : '' }}>
                                <label class="form-check-label" for="global-lock-all">Pilih semua judul</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="global-lock-stories" class="form-label">Judul Novel</label>
                            <div class="input-group mb-2">
                                <input type="text" id="global-lock-story-search" class="form-control"
                                    placeholder="Cari judul novel..." autocomplete="off">
                                <button type="button" id="global-lock-story-search-button" class="btn btn-outline-primary">
                                    <i class="icon-base bx bx-search me-1"></i> Cari
                                </button>
                                <button type="button" id="global-lock-story-reset-button" class="btn btn-outline-secondary">
                                    Reset
                                </button>
                            </div>
                            @php
                                $selectedStoryIds = collect(old('cerita_ids', []))->map(fn ($id) => (string) $id);
                            @endphp
                            <div id="global-lock-selected-stories"></div>
                            <div id="global-lock-stories" class="list-group border rounded overflow-auto"
                                data-current-page="1" data-last-page="{{ $storyPickerLastPage }}" style="max-height:230px;">
                                @foreach($lockPickerCeritas as $selectedCerita)
                                    <label class="list-group-item d-flex align-items-center gap-3">
                                        <input type="checkbox" class="form-check-input m-0" value="{{ $selectedCerita->id }}"
                                            data-story-checkbox="1"
                                            data-judul="{{ $selectedCerita->judul }}"
                                            data-parts="{{ (int) $selectedCerita->parts }}"
                                            {{ old('form_type') === 'global-lock' && $selectedStoryIds->contains((string) $selectedCerita->id) ? 'checked' : '' }}>
                                        <span class="flex-grow-1">{{ $selectedCerita->judul }} ({{ (int) $selectedCerita->parts }} chapter)</span>
                                    </label>
                                @endforeach
                            </div>
                            <div id="global-lock-story-pager" class="d-flex align-items-center justify-content-between gap-2 mt-2">
                                <small id="global-lock-story-count" class="text-muted">0 dipilih</small>
                                <div class="btn-group btn-group-sm" role="group" aria-label="Pagination judul novel">
                                    <button type="button" class="btn btn-outline-secondary" data-story-prev>Prev</button>
                                    <button type="button" class="btn btn-outline-secondary disabled" data-story-page-label>Halaman 1 / {{ $storyPickerLastPage }}</button>
                                    <button type="button" class="btn btn-outline-secondary" data-story-next>Next</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="global-lock-start" class="form-label">Chapter Awal</label>
                            <input type="number" min="1" name="chapter_start" id="global-lock-start"
                                class="form-control" value="{{ old('chapter_start', 1) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label for="global-lock-end" class="form-label">Chapter Akhir</label>
                            <input type="number" min="1" name="chapter_end" id="global-lock-end"
                                class="form-control" value="{{ old('chapter_end', 5) }}" required>
                        </div>
                    </form>
                @endif
            </div>
            @if($hasCeritas)
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="global-lock-form" id="global-lock-submit" class="btn btn-primary">
                        <i class="icon-base bx bx-lock me-1"></i> Lock
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="bulk-pilihan-modal" tabindex="-1" aria-labelledby="bulk-pilihan-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulk-pilihan-modal-title">Set Flag Cerita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                @if(! $hasCeritas)
                    <div class="alert alert-info mb-0">Belum ada cerita untuk dipilih.</div>
                @else
                    <form method="POST" action="{{ route('cerita.bulk-pilihan') }}" id="bulk-pilihan-form" class="row g-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="form_type" value="bulk-pilihan">
                        <input type="hidden" name="bulk_scope" value="selected">

                        <div class="col-md-6">
                            <label class="form-label d-block">Field</label>
                            <div class="btn-group" role="group" aria-label="Pilihan field cerita">
                                <input type="checkbox" class="btn-check" name="bulk_fields[]" id="bulk-field-recomendation"
                                    value="recomendation" {{ collect(old('bulk_fields', ['recomendation']))->contains('recomendation') ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="bulk-field-recomendation">
                                    <i class="icon-base bx bx-star me-1"></i> Rekomendasi
                                </label>

                                <input type="checkbox" class="btn-check" name="bulk_fields[]" id="bulk-field-wajib"
                                    value="wajib_dibaca" {{ collect(old('bulk_fields', []))->contains('wajib_dibaca') ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="bulk-field-wajib">
                                    <i class="icon-base bx bx-bookmark me-1"></i> Wajib Dibaca
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label d-block">Aksi</label>
                            <div class="btn-group" role="group" aria-label="Aksi pilihan cerita">
                                <input type="radio" class="btn-check" name="bulk_action" id="bulk-action-enable"
                                    value="enable" {{ old('bulk_action', 'enable') === 'enable' ? 'checked' : '' }}>
                                <label class="btn btn-outline-success" for="bulk-action-enable">
                                    <i class="icon-base bx bx-check-circle me-1"></i> Aktifkan
                                </label>

                                <input type="radio" class="btn-check" name="bulk_action" id="bulk-action-disable"
                                    value="disable" {{ old('bulk_action') === 'disable' ? 'checked' : '' }}>
                                <label class="btn btn-outline-warning" for="bulk-action-disable">
                                    <i class="icon-base bx bx-x-circle me-1"></i> Nonaktifkan
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="bulk_scope" value="all"
                                    id="bulk-pilihan-all" {{ old('bulk_scope') === 'all' ? 'checked' : '' }}>
                                <label class="form-check-label" for="bulk-pilihan-all">Pilih semua judul</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="bulk-pilihan-stories" class="form-label">Judul Cerita</label>
                            <div class="input-group mb-2">
                                <input type="text" id="bulk-pilihan-story-search" class="form-control"
                                    placeholder="Cari judul cerita..." autocomplete="off">
                                <button type="button" id="bulk-pilihan-story-search-button" class="btn btn-outline-primary">
                                    <i class="icon-base bx bx-search me-1"></i> Cari
                                </button>
                                <button type="button" id="bulk-pilihan-story-reset-button" class="btn btn-outline-secondary">
                                    Reset
                                </button>
                            </div>
                            @php
                                $selectedStoryIds = collect(old('cerita_ids', []))->map(fn ($id) => (string) $id);
                            @endphp
                            <div id="bulk-pilihan-selected-stories"></div>
                            <div id="bulk-pilihan-stories" class="list-group border rounded overflow-auto"
                                data-current-page="1" data-last-page="{{ $storyPickerLastPage }}" style="max-height:230px;">
                                @foreach($bulkPickerCeritas as $selectedCerita)
                                    <label class="list-group-item d-flex align-items-center gap-3">
                                        <input type="checkbox" class="form-check-input m-0" value="{{ $selectedCerita->id }}"
                                            data-story-checkbox="1"
                                            data-judul="{{ $selectedCerita->judul }}"
                                            data-parts="{{ (int) $selectedCerita->parts }}"
                                            {{ old('form_type') === 'bulk-pilihan' && $selectedStoryIds->contains((string) $selectedCerita->id) ? 'checked' : '' }}>
                                        <span class="flex-grow-1">{{ $selectedCerita->judul }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <div id="bulk-pilihan-story-pager" class="d-flex align-items-center justify-content-between gap-2 mt-2">
                                <small id="bulk-pilihan-story-count" class="text-muted">0 dipilih</small>
                                <div class="btn-group btn-group-sm" role="group" aria-label="Pagination judul cerita">
                                    <button type="button" class="btn btn-outline-secondary" data-story-prev>Prev</button>
                                    <button type="button" class="btn btn-outline-secondary disabled" data-story-page-label>Halaman 1 / {{ $storyPickerLastPage }}</button>
                                    <button type="button" class="btn btn-outline-secondary" data-story-next>Next</button>
                                </div>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
            @if($hasCeritas)
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="bulk-pilihan-form" id="bulk-pilihan-submit" class="btn btn-primary">
                        <i class="icon-base bx bx-check-circle me-1"></i> Aktifkan Rekomendasi
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
