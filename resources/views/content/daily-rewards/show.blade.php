@extends('layouts/contentNavbarLayout')
@section('title', 'Detail Reward Harian')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-6">
            <h4 class="mb-0">{{ $dailyReward->title }}</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('daily-rewards.edit', $dailyReward) }}" class="btn btn-primary"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                <a href="{{ route('daily-rewards.index') }}" class="btn btn-outline-secondary"><i class="icon-base bx bx-arrow-back me-1"></i> Kembali</a>
            </div>
        </div>
        <div class="card mb-6">
            <div class="card-header"><h6 class="mb-0">Informasi Reward</h6></div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-3"><small class="text-muted d-block mb-1">Type</small><span class="badge bg-label-info">{{ $dailyReward->type->label ?? '-' }}</span></div>
                    <div class="col-md-3"><small class="text-muted d-block mb-1">Koin</small><span class="badge bg-label-warning">{{ number_format($dailyReward->coin_reward) }} koin</span></div>
                    <div class="col-md-3"><small class="text-muted d-block mb-1">Status</small>@if($dailyReward->status)<span class="badge bg-label-success">Aktif</span>@else<span class="badge bg-label-secondary">Nonaktif</span>@endif</div>
                    <div class="col-md-3"><small class="text-muted d-block mb-1">Total Klaim</small><span class="badge bg-label-primary">{{ $dailyReward->claims->count() }}</span></div>
                    <div class="col-12"><small class="text-muted d-block mb-1">URL Target</small>@if($dailyReward->target_url)<a href="{{ $dailyReward->target_url }}" target="_blank" rel="noopener">{{ $dailyReward->target_url }}</a>@else<span>-</span>@endif</div>
                </div>
            </div>
        </div>
        <div class="card mb-6">
            <div class="card-header"><h6 class="mb-0">Jadwal Video</h6></div>
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead><tr><th>Hari</th><th>Video</th></tr></thead>
                    <tbody>
                        @foreach($days as $dayNumber => $dayLabel)
                        @php($schedule = $dailyReward->videoSchedules->firstWhere('day_of_week', $dayNumber))
                        <tr><td>{{ $dayLabel }}</td><td>{{ $schedule?->video?->title ?? '-' }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Klaim Terakhir</h6></div>
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead><tr><th>User</th><th>Tanggal</th><th>Koin</th><th>Video</th></tr></thead>
                    <tbody>
                        @forelse($dailyReward->claims->sortByDesc('created_at')->take(20) as $claim)
                        <tr><td>{{ $claim->user->name ?? '-' }}</td><td>{{ $claim->claim_date?->format('d M Y') }}</td><td>{{ number_format($claim->coin_reward) }}</td><td>{{ $claim->video->title ?? '-' }}</td></tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-5 text-muted">Belum ada klaim.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
