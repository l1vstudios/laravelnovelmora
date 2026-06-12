@extends('layouts/contentNavbarLayout')
@section('title', 'Detail Master Type')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Detail Master Type</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('reward-types.edit', $rewardType) }}" class="btn btn-primary btn-sm"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                    <a href="{{ route('reward-types.index') }}" class="btn btn-outline-secondary btn-sm"><i class="icon-base bx bx-arrow-back me-1"></i> Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-sm-6"><small class="text-muted d-block mb-1">Kode</small><code>{{ $rewardType->name }}</code></div>
                    <div class="col-sm-6"><small class="text-muted d-block mb-1">Label</small><span class="fw-medium">{{ $rewardType->label }}</span></div>
                    <div class="col-sm-6"><small class="text-muted d-block mb-1">Status</small>@if($rewardType->status)<span class="badge bg-label-success">Aktif</span>@else<span class="badge bg-label-secondary">Nonaktif</span>@endif</div>
                    <div class="col-sm-6"><small class="text-muted d-block mb-1">Reward</small><span class="badge bg-label-primary">{{ $rewardType->daily_rewards_count }} job</span></div>
                    <div class="col-12"><small class="text-muted d-block mb-1">Deskripsi</small><span>{{ $rewardType->description ?: '-' }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
