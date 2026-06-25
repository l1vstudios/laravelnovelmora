@extends('layouts/contentNavbarLayout')
@section('title', 'Master Video')

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible mb-6">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Master Video</h5>
                <a href="{{ route('reward-videos.create') }}" class="btn btn-primary">
                    <i class="icon-base bx bx-plus me-1"></i> Tambah Video
                </a>
            </div>
            <div class="card-body border-bottom pb-4 pt-3">
                <form method="GET" action="{{ route('reward-videos.index') }}" class="row g-3 align-items-end">
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
                        @if(request()->filled('status'))
                            <a href="{{ route('reward-videos.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                            <th>Status</th>
                            <th>Dipakai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($rewardVideos as $video)
                        <tr>
                            <td>{{ $loop->iteration + ($rewardVideos->currentPage() - 1) * $rewardVideos->perPage() }}</td>
                            <td>
                                {{-- PERBAIKAN: Mengambil fisik file MP4 dari storage jika ada, fallback ke URL jika tidak --}}
                                <video src="{{ $video->video_path ? asset('storage/' . $video->video_path) : $video->video_url }}" muted style="width:96px;height:54px;object-fit:cover;" class="rounded bg-label-secondary"></video>
                            </td>
                            <td>
                                <span class="fw-medium">{{ $video->title }}</span>
                                @if($video->video_path)
                                    <small class="text-muted d-block">Upload</small>
                                @endif
                            </td>
                            <td>
                                @if($video->status)
                                    <span class="badge bg-label-success">Aktif</span>
                                @else
                                    <span class="badge bg-label-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td><span class="badge bg-label-primary">{{ $video->schedules_count }} jadwal</span></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="icon-base bx bx-dots-vertical-rounded"></i></button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('reward-videos.show', $video) }}"><i class="icon-base bx bx-show me-1"></i> Detail</a>
                                        <a class="dropdown-item" href="{{ route('reward-videos.edit', $video) }}"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                                        <form action="{{ route('reward-videos.destroy', $video) }}" method="POST" data-confirm="Hapus master video ini?">
                                            @csrf
                                            @method('DELETE')
                                            <button class="dropdown-item text-danger"><i class="icon-base bx bx-trash me-1"></i> Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-6 text-muted">Belum ada master video.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($rewardVideos->hasPages())
                <div class="card-footer d-flex justify-content-end">{{ $rewardVideos->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
