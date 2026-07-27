@php
    $selectedId = old('cerita_id', $selectedCerita?->id);
    $selectedTitle = $selectedCerita?->judul;
@endphp

<div class="mb-5">
    <label class="form-label">Link ke Judul Cerita</label>
    <div class="dropdown js-cerita-picker" data-search-url="{{ route('slider.cerita-options') }}">
        <input type="hidden" name="cerita_id" value="{{ $selectedId }}">
        <button type="button" class="form-select text-start @error('cerita_id') is-invalid @enderror"
            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
            <span class="js-cerita-picker-label">{{ $selectedTitle ?: 'Tidak ada link' }}</span>
        </button>
        <div class="dropdown-menu w-100 p-2">
            <input type="search" class="form-control mb-2 js-cerita-picker-search"
                placeholder="Cari judul cerita..." autocomplete="off">
            <div class="list-group list-group-flush js-cerita-picker-results" style="max-height: 240px; overflow-y: auto;">
                <button type="button" class="list-group-item list-group-item-action js-cerita-picker-option"
                    data-id="" data-title="Tidak ada link">Tidak ada link</button>
                @foreach($ceritas as $cerita)
                    <button type="button" class="list-group-item list-group-item-action js-cerita-picker-option"
                        data-id="{{ $cerita->id }}" data-title="{{ $cerita->judul }}">
                        {{ $cerita->judul }}
                    </button>
                @endforeach
            </div>
            <small class="text-muted d-block mt-2">Maksimal 5 hasil.</small>
        </div>
        @error('cerita_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
</div>

@once
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-cerita-picker').forEach(function (picker) {
        const searchUrl = picker.dataset.searchUrl;
        const hiddenInput = picker.querySelector('input[name="cerita_id"]');
        const toggleButton = picker.querySelector('[data-bs-toggle="dropdown"]');
        const label = picker.querySelector('.js-cerita-picker-label');
        const searchInput = picker.querySelector('.js-cerita-picker-search');
        const results = picker.querySelector('.js-cerita-picker-results');
        let timeoutId = null;

        function selectOption(id, title) {
            hiddenInput.value = id;
            label.textContent = title || 'Tidak ada link';
        }

        function optionButton(item) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'list-group-item list-group-item-action js-cerita-picker-option';
            button.dataset.id = item.id || '';
            button.dataset.title = item.judul || 'Tidak ada link';
            button.textContent = item.judul || 'Tidak ada link';

            return button;
        }

        function renderOptions(items) {
            results.innerHTML = '';
            results.appendChild(optionButton({ id: '', judul: 'Tidak ada link' }));

            if (!items.length) {
                const empty = document.createElement('div');
                empty.className = 'list-group-item text-muted';
                empty.textContent = 'Judul tidak ditemukan';
                results.appendChild(empty);
                return;
            }

            items.slice(0, 5).forEach(function (item) {
                results.appendChild(optionButton(item));
            });
        }

        function loadOptions(keyword) {
            const url = new URL(searchUrl, window.location.origin);
            if (keyword) {
                url.searchParams.set('q', keyword);
            }

            fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(function (response) { return response.json(); })
                .then(function (payload) { renderOptions(payload.data || []); });
        }

        results.addEventListener('click', function (event) {
            const option = event.target.closest('.js-cerita-picker-option');
            if (!option) {
                return;
            }

            selectOption(option.dataset.id, option.dataset.title);

            if (window.bootstrap && toggleButton) {
                window.bootstrap.Dropdown.getOrCreateInstance(toggleButton).hide();
            }
        });

        searchInput.addEventListener('input', function () {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(function () {
                loadOptions(searchInput.value.trim());
            }, 250);
        });
    });
});
</script>
@endonce
