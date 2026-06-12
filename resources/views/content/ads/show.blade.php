@extends('layouts/contentNavbarLayout')
@section('title', 'Detail Ads')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-6">
            <div>
                <h4 class="mb-1">{{ $ad->title }}</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('ads.index') }}">Ads</a></li>
                        <li class="breadcrumb-item active">{{ $ad->title }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('ads.edit', $ad) }}" class="btn btn-primary"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                <form action="{{ route('ads.destroy', $ad) }}" method="POST" data-confirm="Hapus ads ini?">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger"><i class="icon-base bx bx-trash"></i></button>
                </form>
                <a href="{{ route('ads.index') }}" class="btn btn-outline-secondary"><i class="icon-base bx bx-arrow-back me-1"></i> Kembali</a>
            </div>
        </div>

        <div class="card mb-6">
            <div class="card-header"><h6 class="mb-0">Informasi Ads</h6></div>
            <div class="card-body">
                <div class="row g-5">
                    <div class="col-md-6">
                        @if($ad->media_type === 'video')
                            <video src="{{ $ad->media_src }}" class="rounded w-100 bg-label-secondary" style="max-height:320px;object-fit:contain;" controls></video>
                        @else
                            <img src="{{ $ad->media_src }}" alt="{{ $ad->title }}" class="rounded img-fluid" style="max-height:320px;" onerror="this.src='https://placehold.co/640x360'">
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="row g-4">
                            <div class="col-sm-6">
                                <small class="text-muted d-block mb-1">Tipe</small>
                                <span class="badge bg-label-{{ $ad->media_type === 'video' ? 'danger' : 'info' }}">{{ $ad->media_type === 'video' ? 'Video' : 'Gambar' }}</span>
                            </div>
                            <div class="col-sm-6">
                                <small class="text-muted d-block mb-1">Status</small>
                                @if($ad->status)<span class="badge bg-label-success">Aktif</span>@else<span class="badge bg-label-secondary">Nonaktif</span>@endif
                            </div>
                            <div class="col-12">
                                <small class="text-muted d-block mb-1">URL Media</small>
                                <span class="text-break">{{ $ad->media_path ? $ad->media_src : $ad->media_url }}</span>
                                @if($ad->media_path)
                                    <span class="badge bg-label-primary ms-2">Upload</span>
                                @endif
                            </div>
                            <div class="col-12">
                                <small class="text-muted d-block mb-1">URL Direct Link</small>
                                @if($ad->target_url)
                                    <a href="{{ $ad->target_url }}" target="_blank" rel="noopener" class="text-break">{{ $ad->target_url }}</a>
                                @else
                                    <span>-</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0">Penempatan di Cerita</h6></div>
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead><tr><th>Cerita</th><th>Setelah Chapter</th></tr></thead>
                    <tbody>
                        @forelse($ad->placements as $placement)
                        <tr>
                            <td>{{ $placement->cerita->judul ?? '-' }}</td>
                            <td>Chapter {{ $placement->after_chapter }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center py-5 text-muted">Belum dipasang di cerita mana pun.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
