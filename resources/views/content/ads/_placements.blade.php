@php
    $selectedPlacements = old('placements', $selectedPlacements ?? []);
    $globalFlags = old('placement_global', []);
    $placementStories = $ceritas->map(function ($cerita) {
        return [
            'id' => (int) $cerita->id,
            'title' => $cerita->judul,
            'chapter_total' => max((int) $cerita->parts, count($cerita->isi_cerita ?? [])),
        ];
    })->values();
    $storiesById = $placementStories->keyBy('id');
    $initialPlacements = [];

    foreach ((array) $selectedPlacements as $ceritaId => $positions) {
        $story = $storiesById->get((int) $ceritaId);

        if (!$story || !is_array($positions)) {
            continue;
        }

        $normalizedPositions = array_intersect_key($positions, array_flip(['before', 'after']));

        if (!$normalizedPositions && array_is_list($positions)) {
            $normalizedPositions = ['after' => $positions];
        }

        foreach ($normalizedPositions as $position => $chapters) {
            if (!in_array($position, ['before', 'after'], true) || !is_array($chapters)) {
                continue;
            }

            foreach ($chapters as $chapter) {
                $isGlobal = false;
                $chapterNumber = is_array($chapter) ? (int) ($chapter['chapter'] ?? 0) : (int) $chapter;

                if (is_array($chapter)) {
                    $isGlobal = (bool) ($chapter['is_global'] ?? false);
                }

                if ($chapterNumber < 1 || $chapterNumber > $story['chapter_total']) {
                    continue;
                }

                $initialPlacements[] = [
                    'story_id' => $story['id'],
                    'story_title' => $story['title'],
                    'position' => $position,
                    'chapter' => $chapterNumber,
                    'is_global' => $isGlobal || isset($globalFlags[$story['id']][$position][$chapterNumber]),
                ];
            }
        }
    }
@endphp

<div class="col-12">
    <h6 class="mb-3 text-muted text-uppercase" style="font-size:.75rem;letter-spacing:.08em;">Hubungkan ke Chapter</h6>

    @if($placementStories->isEmpty())
        <div class="alert alert-info mb-0">Belum ada cerita untuk dipasangkan ads.</div>
    @else
        <div id="ads-placement-picker" class="border rounded p-4">
            <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" id="placement-all-stories">
                <label class="form-check-label" for="placement-all-stories">Pilih semua cerita</label>
            </div>

            <div class="row g-4 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Judul Novel</label>
                    <div class="position-relative">
                        <input type="text" id="placement-story-search" class="form-control" placeholder="Cari judul novel..." autocomplete="off">
                        <div id="placement-story-menu" class="dropdown-menu w-100 mt-1" style="max-height:220px;overflow:auto;"></div>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Posisi</label>
                    <select id="placement-position" class="form-select">
                        <option value="after">Setelah</option>
                        <option value="before">Sebelum</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Chapter</label>
                    <div class="position-relative">
                        <input type="text" id="placement-chapter-search" class="form-control" placeholder="Pilih novel dulu" autocomplete="off" disabled>
                        <div id="placement-chapter-menu" class="dropdown-menu w-100 mt-1" style="max-height:220px;overflow:auto;"></div>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="button" id="placement-add" class="btn btn-primary w-100">
                        <i class="icon-base bx bx-plus me-1"></i> Tambah
                    </button>
                </div>
            </div>

            <div class="mt-4">
                <label class="form-label">Pilihan Ads</label>
                <div id="placement-empty" class="text-muted small">Belum ada chapter dipilih.</div>
                <div id="placement-selected" class="d-flex flex-wrap gap-2"></div>
            </div>
        </div>
    @endif
</div>

@if($placementStories->isNotEmpty())
<script>
(function () {
    const stories = @json($placementStories);
    const initialPlacements = @json($initialPlacements);
    const selected = new Map();
    let selectedStory = null;
    let selectedChapters = new Set();

    const storyInput = document.getElementById('placement-story-search');
    const storyMenu = document.getElementById('placement-story-menu');
    const allStoriesInput = document.getElementById('placement-all-stories');
    const positionInput = document.getElementById('placement-position');
    const chapterInput = document.getElementById('placement-chapter-search');
    const chapterMenu = document.getElementById('placement-chapter-menu');
    const addButton = document.getElementById('placement-add');
    const selectedWrap = document.getElementById('placement-selected');
    const emptyState = document.getElementById('placement-empty');

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function key(storyId, position, chapter) {
        return `${storyId}:${position}:${chapter}`;
    }

    function positionLabel(position) {
        return position === 'before' ? 'Sebelum' : 'Setelah';
    }

    function showMenu(menu) {
        menu.classList.add('show');
    }

    function hideMenus() {
        storyMenu.classList.remove('show');
        chapterMenu.classList.remove('show');
    }

    function selectedAllStories() {
        return allStoriesInput.checked;
    }

    function eligibleStories(chapter) {
        if (!selectedAllStories()) {
            return selectedStory && chapter <= selectedStory.chapter_total ? [selectedStory] : [];
        }

        return stories.filter((story) => chapter <= story.chapter_total);
    }

    function chapterLimit() {
        if (selectedAllStories()) {
            return Math.max(...stories.map((story) => story.chapter_total));
        }

        return selectedStory ? selectedStory.chapter_total : 0;
    }

    function renderStoryMenu(search = '') {
        if (selectedAllStories()) {
            return;
        }

        const needle = search.trim().toLowerCase();
        const filtered = stories.filter((story) => story.title.toLowerCase().includes(needle));

        storyMenu.innerHTML = filtered.length
            ? filtered.map((story) => `
                <button type="button" class="dropdown-item" data-story-id="${story.id}">
                    <span class="fw-medium">${escapeHtml(story.title)}</span>
                    <small class="text-muted d-block">${story.chapter_total} chapter</small>
                </button>
            `).join('')
            : '<span class="dropdown-item-text text-muted">Novel tidak ditemukan.</span>';

        showMenu(storyMenu);
    }

    function renderChapterMenu(search = '') {
        const limit = chapterLimit();

        if (!limit) {
            return;
        }

        const needle = search.trim().toLowerCase();
        const chapters = [];
        const position = positionInput.value;

        for (let chapter = 1; chapter <= limit; chapter++) {
            const storyCount = eligibleStories(chapter).length;

            if (!storyCount) {
                continue;
            }

            const label = `${positionLabel(position)} Chapter ${chapter}`;
            if (!needle || label.toLowerCase().includes(needle) || String(chapter).includes(needle)) {
                chapters.push({ chapter, label, storyCount });
            }
        }

        chapterMenu.innerHTML = chapters.length
            ? chapters.map((item) => `
                <label class="dropdown-item d-flex align-items-center gap-2 mb-0" data-chapter-option="${item.chapter}">
                    <input type="checkbox" class="form-check-input m-0 placement-chapter-check"
                        value="${item.chapter}" ${selectedChapters.has(item.chapter) ? 'checked' : ''}>
                    <span>${escapeHtml(item.label)}</span>
                    ${selectedAllStories() ? `<small class="text-muted ms-auto">${item.storyCount} cerita</small>` : ''}
                </label>
            `).join('')
            : '<span class="dropdown-item-text text-muted">Chapter tidak ditemukan.</span>';

        showMenu(chapterMenu);
    }

    function renderSelected() {
        selectedWrap.innerHTML = Array.from(selected.values()).map((item) => `
            <span class="badge bg-label-primary d-inline-flex align-items-center gap-2 px-3 py-2">
                ${escapeHtml(item.story_title)} - ${positionLabel(item.position)} Chapter ${item.chapter}
                ${item.is_global ? '<span class="badge bg-primary">Global</span>' : ''}
                <button type="button" class="btn btn-sm p-0 text-primary" data-remove="${item.story_id}:${item.position}:${item.chapter}" aria-label="Hapus">
                    <i class="icon-base bx bx-x"></i>
                </button>
                <input type="hidden" name="placements[${item.story_id}][${item.position}][]" value="${item.chapter}">
                ${item.is_global ? `<input type="hidden" name="placement_global[${item.story_id}][${item.position}][${item.chapter}]" value="1">` : ''}
            </span>
        `).join('');

        emptyState.style.display = selected.size ? 'none' : 'block';
    }

    function resetChapterPicker() {
        selectedChapters = new Set();
        chapterInput.value = '';
        chapterInput.disabled = !selectedAllStories() && !selectedStory;
        chapterInput.placeholder = selectedAllStories() || selectedStory ? 'Cari dan pilih chapter...' : 'Pilih novel dulu';
        chapterMenu.innerHTML = '';
    }

    function updateChapterInputLabel() {
        if (!selectedChapters.size) {
            chapterInput.value = '';
            return;
        }

        const chapters = Array.from(selectedChapters).sort((a, b) => a - b);
        chapterInput.value = chapters.length === 1
            ? `${positionLabel(positionInput.value)} Chapter ${chapters[0]}`
            : `${chapters.length} chapter dipilih`;
    }

    initialPlacements.forEach((item) => {
        selected.set(key(item.story_id, item.position, item.chapter), item);
    });
    renderSelected();

    storyInput.addEventListener('focus', () => renderStoryMenu(storyInput.value));
    storyInput.addEventListener('input', () => {
        selectedStory = null;
        storyInput.dataset.storyId = '';
        resetChapterPicker();
        renderStoryMenu(storyInput.value);
    });

    storyMenu.addEventListener('click', (event) => {
        const button = event.target.closest('[data-story-id]');
        if (!button) return;

        selectedStory = stories.find((story) => String(story.id) === button.dataset.storyId);
        storyInput.value = selectedStory.title;
        storyInput.dataset.storyId = selectedStory.id;
        storyMenu.classList.remove('show');
        resetChapterPicker();
        chapterInput.focus();
        renderChapterMenu();
    });

    allStoriesInput.addEventListener('change', () => {
        selectedStory = null;
        storyInput.value = selectedAllStories() ? 'Semua cerita' : '';
        storyInput.disabled = selectedAllStories();
        storyInput.dataset.storyId = '';
        hideMenus();
        resetChapterPicker();
    });

    positionInput.addEventListener('change', () => {
        updateChapterInputLabel();
        renderChapterMenu(chapterInput.value);
    });

    chapterInput.addEventListener('focus', () => renderChapterMenu(chapterInput.value));
    chapterInput.addEventListener('input', () => {
        renderChapterMenu(chapterInput.value);
    });

    chapterMenu.addEventListener('change', (event) => {
        const checkbox = event.target.closest('.placement-chapter-check');
        if (!checkbox) return;

        const chapter = Number(checkbox.value);
        if (checkbox.checked) {
            selectedChapters.add(chapter);
        } else {
            selectedChapters.delete(chapter);
        }

        updateChapterInputLabel();
    });

    addButton.addEventListener('click', () => {
        if ((!selectedStory && !selectedAllStories()) || !selectedChapters.size) {
            return;
        }

        const position = positionInput.value;

        selectedChapters.forEach((chapter) => {
            eligibleStories(chapter).forEach((story) => {
                const placementKey = key(story.id, position, chapter);
                const existing = selected.get(placementKey);

                selected.set(placementKey, {
                    story_id: story.id,
                    story_title: story.title,
                    position,
                    chapter,
                    is_global: selectedAllStories() || Boolean(existing?.is_global),
                });
            });
        });

        renderSelected();
        resetChapterPicker();
    });

    selectedWrap.addEventListener('click', (event) => {
        const button = event.target.closest('[data-remove]');
        if (!button) return;

        selected.delete(button.dataset.remove);
        renderSelected();
    });

    document.addEventListener('click', (event) => {
        if (!document.getElementById('ads-placement-picker').contains(event.target)) {
            hideMenus();
        }
    });
})();
</script>
@endif
