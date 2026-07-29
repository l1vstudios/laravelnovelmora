@php
    $selectedSchedules = $dailyReward?->videoSchedules
        ?->groupBy('day_of_week')
        ->map(fn ($schedules) => $schedules->pluck('reward_video_id')->map(fn ($id) => (string) $id)->all())
        ->toArray() ?? [];
    $oldSchedules = old('video_schedules', $selectedSchedules);
@endphp

<div class="row g-5">
    <div class="col-md-8">
        <label class="form-label">Judul Reward <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $dailyReward->title ?? '') }}" placeholder="Nonton iklan harian" autofocus>
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Koin <span class="text-danger">*</span></label>
        <input type="number" name="coin_reward" min="0" max="4294967295" class="form-control @error('coin_reward') is-invalid @enderror" value="{{ old('coin_reward', $dailyReward->coin_reward ?? 0) }}">
        @error('coin_reward')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Reward Type <span class="text-danger">*</span></label>
        <select name="reward_type_id" class="form-select @error('reward_type_id') is-invalid @enderror">
            <option value="">-- Pilih Type --</option>
            @foreach($rewardTypes as $type)
                <option value="{{ $type->id }}" {{ old('reward_type_id', $dailyReward->reward_type_id ?? '') == $type->id ? 'selected' : '' }}>
                    {{ $type->label }} ({{ $type->name }})
                </option>
            @endforeach
        </select>
        @error('reward_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">URL Target</label>
        <input type="url" name="target_url" id="target_url" class="form-control @error('target_url') is-invalid @enderror" value="{{ old('target_url', $dailyReward->target_url ?? '') }}" placeholder="https://...">
        @error('target_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <h6 class="mb-3 text-muted text-uppercase" style="font-size:.75rem;letter-spacing:.08em;">Jadwal Video Nonton Iklan</h6>
        <div class="row g-4">
            @foreach($days as $dayNumber => $dayLabel)
            @php($selectedForDay = collect($oldSchedules[$dayNumber] ?? [])->map(fn ($id) => (string) $id)->all())
            <div class="col-md-6">
                <label class="form-label">{{ $dayLabel }}</label>
                <div class="dropdown js-video-schedule">
                    <button class="btn btn-outline-secondary dropdown-toggle w-100 d-flex align-items-center justify-content-between text-start" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        <span class="js-video-schedule-label text-truncate">Tidak ada video</span>
                    </button>
                    <div class="dropdown-menu w-100 p-2" style="max-height:260px;overflow:auto;">
                        @foreach($rewardVideos as $video)
                            <label class="dropdown-item d-flex align-items-center gap-2 mb-0">
                                <input class="form-check-input m-0 js-video-schedule-input" type="checkbox" name="video_schedules[{{ $dayNumber }}][]" value="{{ $video->id }}" data-video-url="{{ $video->video_target_url }}" data-video-title="{{ $video->title }}" {{ in_array((string) $video->id, $selectedForDay, true) ? 'checked' : '' }}>
                                <span class="text-truncate">{{ $video->title }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="status" value="0">
            <input class="form-check-input" type="checkbox" name="status" id="status" value="1" {{ old('status', $dailyReward->status ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="status">Status Aktif</label>
        </div>
    </div>
</div>

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const targetInput = document.getElementById('target_url');
    const videoDropdowns = document.querySelectorAll('.js-video-schedule');

    const checkedInputs = (dropdown) => {
        return Array.from(dropdown.querySelectorAll('.js-video-schedule-input:checked'));
    };

    const selectedUrls = (dropdown) => {
        return checkedInputs(dropdown)
            .map((input) => input.dataset.videoUrl)
            .filter(Boolean);
    };

    const firstSelectedVideoUrl = () => {
        for (const dropdown of videoDropdowns) {
            const urls = selectedUrls(dropdown);
            if (urls.length) return urls[0];
        }

        return '';
    };

    const updateDropdownLabel = (dropdown) => {
        const label = dropdown.querySelector('.js-video-schedule-label');
        const titles = checkedInputs(dropdown).map((input) => input.dataset.videoTitle).filter(Boolean);

        label.textContent = titles.length ? titles.join(', ') : 'Tidak ada video';
        label.title = label.textContent;
    };

    videoDropdowns.forEach((dropdown) => {
        updateDropdownLabel(dropdown);

        dropdown.addEventListener('change', function () {
            updateDropdownLabel(this);
            const selectedUrl = selectedUrls(this)[0];
            targetInput.value = selectedUrl || firstSelectedVideoUrl() || targetInput.value;
        });
    });

    if (!targetInput.value) {
        targetInput.value = firstSelectedVideoUrl();
    }
});
</script>
@endsection
