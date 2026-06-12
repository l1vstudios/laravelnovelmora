@php
    $selectedPlacements = $selectedPlacements ?? [];
@endphp

<div class="col-12">
    <h6 class="mb-3 text-muted text-uppercase" style="font-size:.75rem;letter-spacing:.08em;">Hubungkan ke Chapter</h6>
    @forelse($ceritas as $cerita)
        @php
            $chapterTotal = max((int) $cerita->parts, count($cerita->isi_cerita ?? []));
            $selectedChapters = old('placements.' . $cerita->id, $selectedPlacements[$cerita->id] ?? []);
            $selectedChapters = array_map('strval', (array) $selectedChapters);
        @endphp
        <div class="border rounded p-4 mb-3">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="fw-medium">{{ $cerita->judul }}</span>
                <span class="badge bg-label-info">{{ $chapterTotal }} chapter</span>
            </div>
            @if($chapterTotal > 0)
                <div class="d-flex flex-wrap gap-3">
                    @for($chapter = 1; $chapter <= $chapterTotal; $chapter++)
                        <div class="form-check form-check-inline mb-1">
                            <input class="form-check-input" type="checkbox"
                                name="placements[{{ $cerita->id }}][]"
                                value="{{ $chapter }}"
                                id="placement_{{ $cerita->id }}_{{ $chapter }}"
                                {{ in_array((string) $chapter, $selectedChapters, true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="placement_{{ $cerita->id }}_{{ $chapter }}">
                                Setelah Chapter {{ $chapter }}
                            </label>
                        </div>
                    @endfor
                </div>
            @else
                <small class="text-muted">Cerita ini belum punya chapter.</small>
            @endif
        </div>
    @empty
        <div class="alert alert-info mb-0">Belum ada cerita untuk dipasangkan ads.</div>
    @endforelse
</div>
