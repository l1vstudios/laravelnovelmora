@php
    $selectedSchedules = $dailyReward?->videoSchedules?->pluck('reward_video_id', 'day_of_week')->toArray() ?? [];
@endphp

<div class="row g-5">
    <div class="col-md-8">
        <label class="form-label">Judul Reward <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $dailyReward->title ?? '') }}" placeholder="Nonton iklan harian" autofocus>
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Koin <span class="text-danger">*</span></label>
        <input type="number" name="coin_reward" min="0" class="form-control @error('coin_reward') is-invalid @enderror" value="{{ old('coin_reward', $dailyReward->coin_reward ?? 0) }}">
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
        <input type="url" name="target_url" class="form-control @error('target_url') is-invalid @enderror" value="{{ old('target_url', $dailyReward->target_url ?? '') }}" placeholder="https://instagram.com/...">
        @error('target_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <h6 class="mb-3 text-muted text-uppercase" style="font-size:.75rem;letter-spacing:.08em;">Jadwal Video Nonton Iklan</h6>
        <div class="row g-4">
            @foreach($days as $dayNumber => $dayLabel)
            <div class="col-md-6">
                <label class="form-label">{{ $dayLabel }}</label>
                <select name="video_schedules[{{ $dayNumber }}]" class="form-select">
                    <option value="">-- Tidak ada video --</option>
                    @foreach($rewardVideos as $video)
                        <option value="{{ $video->id }}" {{ old('video_schedules.' . $dayNumber, $selectedSchedules[$dayNumber] ?? '') == $video->id ? 'selected' : '' }}>
                            {{ $video->title }}
                        </option>
                    @endforeach
                </select>
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
