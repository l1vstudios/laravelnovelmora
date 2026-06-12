@extends('layouts/contentNavbarLayout')
@section('title', 'Detail Master Video')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-6">
            <h4 class="mb-0">{{ $rewardVideo->title }}</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('reward-videos.edit', $rewardVideo) }}" class="btn btn-primary"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                <a href="{{ route('reward-videos.index') }}" class="btn btn-outline-secondary"><i class="icon-base bx bx-arrow-back me-1"></i> Kembali</a>
            </div>
        </div>
        <div class="card mb-6">
            <div class="card-body">
                <video src="{{ $rewardVideo->video_src }}" class="rounded w-100 bg-label-secondary mb-4" style="max-height:360px;object-fit:contain;" controls></video>
                <div class="row g-4">
                    <div class="col-md-4"><small class="text-muted d-block mb-1">Status</small>@if($rewardVideo->status)<span class="badge bg-label-success">Aktif</span>@else<span class="badge bg-label-secondary">Nonaktif</span>@endif</div>
                    <div class="col-md-8"><small class="text-muted d-block mb-1">Source</small><span class="text-break">{{ $rewardVideo->video_path ? $rewardVideo->video_src : $rewardVideo->video_url }}</span></div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Dipakai di Reward</h6></div>
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead><tr><th>Reward</th><th>Hari</th></tr></thead>
                    <tbody>
                        @php($days = [1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu',7=>'Minggu'])
                        @forelse($rewardVideo->schedules as $schedule)
                        <tr><td>{{ $schedule->dailyReward->title ?? '-' }}</td><td>{{ $days[$schedule->day_of_week] ?? '-' }}</td></tr>
                        @empty
                        <tr><td colspan="2" class="text-center py-5 text-muted">Belum dipakai di jadwal reward.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
