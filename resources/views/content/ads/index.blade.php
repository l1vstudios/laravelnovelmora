@extends('layouts/contentNavbarLayout')
@section('title', 'Ads')

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible mb-6" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Master Ads</h5>
                <a href="{{ route('ads.create') }}" class="btn btn-primary">
                    <i class="icon-base bx bx-plus me-1"></i> Tambah Ads
                </a>
            </div>
            <div class="card-body border-bottom pb-4 pt-3">
                <form method="GET" action="{{ route('ads.index') }}" class="row g-3 align-items-end">
                    <div class="col-auto">
                        <label class="form-label mb-1 text-muted" style="font-size:.75rem;">Tipe</label>
                        <select name="media_type" class="form-select form-select-sm" style="min-width:130px;">
                            <option value="">Semua</option>
                            <option value="image" {{ request('media_type') === 'image' ? 'selected' : '' }}>Gambar</option>
                            <option value="video" {{ request('media_type') === 'video' ? 'selected' : '' }}>Video</option>
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
                        @if(request()->hasAny(['media_type','status']))
                        <a href="{{ route('ads.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Preview</th>
                            <th>Judul</th>
                            <th>Tipe</th>
                            <th>Status</th>
                            <th>Dipakai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($ads as $ad)
                        <tr>
                            <td>{{ $loop->iteration + ($ads->currentPage() - 1) * $ads->perPage() }}</td>
                            <td>
                                @if($ad->media_type === 'video')
                                    <video src="{{ $ad->media_url }}" muted style="width:90px;height:54px;object-fit:cover;" class="rounded bg-label-secondary"></video>
                                @else
                                    <img src="{{ $ad->media_url }}" alt="{{ $ad->title }}" class="rounded" style="width:90px;height:54px;object-fit:cover;" onerror="this.src='https://placehold.co/90x54'">
                                @endif
                            </td>
                            <td>
                                <span class="fw-medium">{{ $ad->title }}</span>
                                @if($ad->target_url)
                                <small class="text-muted d-block">{{ Str::limit($ad->target_url, 48) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($ad->media_type === 'video')
                                    <span class="badge bg-label-danger">Video</span>
                                @else
                                    <span class="badge bg-label-info">Gambar</span>
                                @endif
                            </td>
                            <td>
                                @if($ad->status)
                                    <span class="badge bg-label-success">Aktif</span>
                                @else
                                    <span class="badge bg-label-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td><span class="badge bg-label-primary">{{ $ad->placements_count }} posisi</span></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="icon-base bx bx-dots-vertical-rounded"></i></button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('ads.show', $ad) }}"><i class="icon-base bx bx-show me-1"></i> Detail</a>
                                        <a class="dropdown-item" href="{{ route('ads.edit', $ad) }}"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                                        <form action="{{ route('ads.destroy', $ad) }}" method="POST" data-confirm="Hapus ads ini?">
                                            @csrf @method('DELETE')
                                            <button class="dropdown-item text-danger"><i class="icon-base bx bx-trash me-1"></i> Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-6 text-muted">Belum ada ads. <a href="{{ route('ads.create') }}">Tambah sekarang</a>.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($ads->hasPages())<div class="card-footer d-flex justify-content-end">{{ $ads->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
