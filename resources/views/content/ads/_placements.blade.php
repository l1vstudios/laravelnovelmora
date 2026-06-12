@php
    $selectedPlacements = old('placements', $selectedPlacements ?? []);
    $placementStories = $ceritas->map(function ($cerita) {
        return [
            'id' => (int) $cerita->id,
            'title' => $cerita->judul,
            'chapter_total' => max((int) $cerita->parts, count($cerita->isi_cerita ?? [])),
        ];
    })->values();
    $storiesById = $placementStories->keyBy('id');
    $initialPlacements = [];

    foreach ((array) $selectedPlacements as $ceritaId => $chapters) {
        $story = $storiesById->get((int) $ceritaId);

        if (!$story) {
            continue;
        }

        foreach ((array) $chapters as $chapter) {
            $chapterNumber = (int) $chapter;

            if ($chapterNumber < 1 || $chapterNumber > $story['chapter_total']) {
                continue;
            }

            $initialPlacements[] = [
                'story_id' => $story['id'],
                'story_title' => $story['title'],
                'chapter' => $chapterNumber,
            ];
        }
    }
@endphp

<div class="col-12">
    <h6 class="mb-3 text-muted text-uppercase" style="font-size:.75rem;letter-spacing:.08em;">Hubungkan ke Chapter</h6>

    @if($placementStories->isEmpty())
        <div class="alert alert-info mb-0">Belum ada cerita untuk dipasangkan ads.</div>
    @else
        <div id="ads-placement-picker" class="border rounded p-4">
            <div class="row g-4 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Judul Novel</label>
                    <div class="position-relative">
                        <input type="text" id="placement-story-search" class="form-control" placeholder="Cari judul novel..." autocomplete="off">
                        <div id="placement-story-menu" class="dropdown-menu w-100 mt-1" style="max-height:220px;overflow:auto;"></div>
                    </div>
                </div>
                <div class="col-md-4">
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
    let selectedChapter = null;

    const storyInput = document.getElementById('placement-story-search');
    const storyMenu = document.getElementById('placement-story-menu');
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

    function key(storyId, chapter) {
        return `${storyId}:${chapter}`;
    }

    function showMenu(menu) {
        menu.classList.add('show');
    }

    function hideMenus() {
        storyMenu.classList.remove('show');
        chapterMenu.classList.remove('show');
    }

    function renderStoryMenu(search = '') {
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
        if (!selectedStory) {
            return;
        }

        const needle = search.trim().toLowerCase();
        const chapters = [];

        for (let chapter = 1; chapter <= selectedStory.chapter_total; chapter++) {
            const label = `Setelah Chapter ${chapter}`;
            if (!needle || label.toLowerCase().includes(needle) || String(chapter).includes(needle)) {
                chapters.push({ chapter, label });
            }
        }

        chapterMenu.innerHTML = chapters.length
            ? chapters.map((item) => `
                <button type="button" class="dropdown-item" data-chapter="${item.chapter}">
                    ${escapeHtml(item.label)}
                </button>
            `).join('')
            : '<span class="dropdown-item-text text-muted">Chapter tidak ditemukan.</span>';

        showMenu(chapterMenu);
    }

    function renderSelected() {
        selectedWrap.innerHTML = Array.from(selected.values()).map((item) => `
            <span class="badge bg-label-primary d-inline-flex align-items-center gap-2 px-3 py-2">
                ${escapeHtml(item.story_title)} - Setelah Chapter ${item.chapter}
                <button type="button" class="btn btn-sm p-0 text-primary" data-remove="${item.story_id}:${item.chapter}" aria-label="Hapus">
                    <i class="icon-base bx bx-x"></i>
                </button>
                <input type="hidden" name="placements[${item.story_id}][]" value="${item.chapter}">
            </span>
        `).join('');

        emptyState.style.display = selected.size ? 'none' : 'block';
    }

    function resetChapterPicker() {
        selectedChapter = null;
        chapterInput.value = '';
        chapterInput.disabled = !selectedStory;
        chapterInput.placeholder = selectedStory ? 'Cari chapter...' : 'Pilih novel dulu';
        chapterMenu.innerHTML = '';
    }

    initialPlacements.forEach((item) => {
        selected.set(key(item.story_id, item.chapter), item);
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

    chapterInput.addEventListener('focus', () => renderChapterMenu(chapterInput.value));
    chapterInput.addEventListener('input', () => {
        selectedChapter = null;
        renderChapterMenu(chapterInput.value);
    });

    chapterMenu.addEventListener('click', (event) => {
        const button = event.target.closest('[data-chapter]');
        if (!button) return;

        selectedChapter = Number(button.dataset.chapter);
        chapterInput.value = `Setelah Chapter ${selectedChapter}`;
        chapterMenu.classList.remove('show');
    });

    addButton.addEventListener('click', () => {
        if (!selectedStory || !selectedChapter) {
            return;
        }

        selected.set(key(selectedStory.id, selectedChapter), {
            story_id: selectedStory.id,
            story_title: selectedStory.title,
            chapter: selectedChapter,
        });

        renderSelected();
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
