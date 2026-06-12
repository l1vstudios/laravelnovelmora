@extends('layouts/contentNavbarLayout')
@section('title', 'Reward Harian')

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('success'))<div class="alert alert-success alert-dismissible mb-6">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
        @if(session('error'))<div class="alert alert-danger alert-dismissible mb-6">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Reward Harian</h5>
                <a href="{{ route('daily-rewards.create') }}" class="btn btn-primary"><i class="icon-base bx bx-plus me-1"></i> Tambah Reward</a>
            </div>
            <div class="card-body border-bottom pb-4 pt-3">
                <form method="GET" action="{{ route('daily-rewards.index') }}" class="row g-3 align-items-end">
                    <div class="col-auto">
                        <label class="form-label mb-1 text-muted" style="font-size:.75rem;">Type</label>
                        <select name="reward_type_id" class="form-select form-select-sm" style="min-width:180px;">
                            <option value="">Semua</option>
                            @foreach($rewardTypes as $type)
                                <option value="{{ $type->id }}" {{ request('reward_type_id') == $type->id ? 'selected' : '' }}>{{ $type->label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label mb-1 text-muted" style="font-size:.75rem;">Status</label>
                        <select name="status" class="form-select form-select-sm" style="min-width:130px;">
                            <option value="">Semua</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-auto d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="icon-base bx bx-filter me-1"></i> Filter</button>
                        @if(request()->hasAny(['reward_type_id','status']))<a href="{{ route('daily-rewards.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>@endif
                    </div>
                </form>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead><tr><th>#</th><th>Reward</th><th>Type</th><th>Koin</th><th>Video Hari Ini</th><th>Status</th><th>Klaim</th><th>Aksi</th></tr></thead>
                    <tbody class="table-border-bottom-0">
                        @php($today = (int) now()->isoWeekday())
                        @forelse($dailyRewards as $reward)
                        <tr>
                            <td>{{ $loop->iteration + ($dailyRewards->currentPage() - 1) * $dailyRewards->perPage() }}</td>
                            <td><span class="fw-medium">{{ $reward->title }}</span>@if($reward->target_url)<small class="text-muted d-block">{{ Str::limit($reward->target_url, 48) }}</small>@endif</td>
                            <td><span class="badge bg-label-info">{{ $reward->type->label ?? '-' }}</span></td>
                            <td><span class="badge bg-label-warning">{{ number_format($reward->coin_reward) }} koin</span></td>
                            <td>{{ $reward->videoForDay($today)?->title ?? '-' }}</td>
                            <td>@if($reward->status)<span class="badge bg-label-success">Aktif</span>@else<span class="badge bg-label-secondary">Nonaktif</span>@endif</td>
                            <td><span class="badge bg-label-primary">{{ $reward->claims_count }}</span></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="icon-base bx bx-dots-vertical-rounded"></i></button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('daily-rewards.show', $reward) }}"><i class="icon-base bx bx-show me-1"></i> Detail</a>
                                        <a class="dropdown-item" href="{{ route('daily-rewards.edit', $reward) }}"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                                        <form action="{{ route('daily-rewards.claim', $reward) }}" method="POST">@csrf<button class="dropdown-item"><i class="icon-base bx bx-coin me-1"></i> Test Klaim</button></form>
                                        <form action="{{ route('daily-rewards.destroy', $reward) }}" method="POST" data-confirm="Hapus reward harian ini?">@csrf @method('DELETE')<button class="dropdown-item text-danger"><i class="icon-base bx bx-trash me-1"></i> Hapus</button></form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center py-6 text-muted">Belum ada reward harian.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($dailyRewards->hasPages())<div class="card-footer d-flex justify-content-end">{{ $dailyRewards->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
