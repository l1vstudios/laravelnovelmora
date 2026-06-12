@extends('layouts/contentNavbarLayout')
@section('title', $cerita->judul)

@section('content')
<div class="row">
    <div class="col-12">

        <div class="d-flex align-items-center justify-content-between mb-6">
            <div>
                <h4 class="mb-1">{{ $cerita->judul }}</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('cerita.index') }}">Cerita</a></li>
                        <li class="breadcrumb-item active">{{ $cerita->judul }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('cerita.edit', $cerita) }}" class="btn btn-primary">
                    <i class="icon-base bx bx-edit-alt me-1"></i> Edit
                </a>
                <form action="{{ route('cerita.destroy', $cerita) }}" method="POST"
                    data-confirm="Hapus cerita ini?">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="icon-base bx bx-trash"></i>
                    </button>
                </form>
                <a href="{{ route('cerita.index') }}" class="btn btn-outline-secondary">
                    <i class="icon-base bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>

        {{-- Info Card --}}
        <div class="card mb-6">
            <div class="card-header"><h6 class="mb-0">Informasi Cerita</h6></div>
            <div class="card-body">
                <div class="d-flex gap-5 mb-5">
                    {{-- Cover --}}
                    @if($cerita->cover)
                        <img src="{{ asset('storage/' . $cerita->cover) }}" alt="{{ $cerita->judul }}"
                            class="rounded shadow-sm flex-shrink-0"
                            style="width:130px;height:180px;object-fit:cover;">
                    @else
                        <div class="rounded border bg-label-secondary d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width:130px;height:180px;">
                            <div class="text-center text-muted">
                                <i class="icon-base bx bx-image" style="font-size:2rem;"></i>
                                <div style="font-size:.75rem;">No Cover</div>
                            </div>
                        </div>
                    @endif
                    <div class="flex-fill">
                <div class="row g-4">
                    <div class="col-sm-6 col-md-3">
                        <small class="text-muted d-block mb-1">Judul</small>
                        <span class="fw-medium">{{ $cerita->judul }}</span>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <small class="text-muted d-block mb-1">Kategori</small>
                        <span class="fw-medium">{{ $cerita->kategori->default_title ?? '-' }}</span>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <small class="text-muted d-block mb-1">Total Chapter</small>
                        <span class="badge bg-label-info">{{ $cerita->parts }} chapter</span>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <small class="text-muted d-block mb-1">Status</small>
                        @if($cerita->status)
                            <span class="badge bg-label-success">Aktif</span>
                        @else
                            <span class="badge bg-label-secondary">Nonaktif</span>
                        @endif
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <small class="text-muted d-block mb-1">Total Dibaca</small>
                        <span class="fw-medium">{{ number_format($cerita->total_read) }}</span>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <small class="text-muted d-block mb-1">Total Vote</small>
                        <span class="fw-medium">{{ number_format($cerita->total_vote) }}</span>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <small class="text-muted d-block mb-1">Rekomendasi</small>
                        @if($cerita->recomendation)
                            <span class="badge bg-label-primary">Ya</span>
                        @else
                            <span class="badge bg-label-secondary">Tidak</span>
                        @endif
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <small class="text-muted d-block mb-1">Wajib Dibaca</small>
                        @if($cerita->wajib_dibaca)
                            <span class="badge bg-label-warning">Ya</span>
                        @else
                            <span class="badge bg-label-secondary">Tidak</span>
                        @endif
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <small class="text-muted d-block mb-1">Ditambahkan</small>
                        <span class="fw-medium">{{ $cerita->created_at ? $cerita->created_at->format('d M Y') : '-' }}</span>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <small class="text-muted d-block mb-1">Diperbarui</small>
                        <span class="fw-medium">{{ $cerita->updated_at ? $cerita->updated_at->format('d M Y') : '-' }}</span>
                    </div>
                </div>
                    </div>{{-- /flex-fill --}}
                </div>{{-- /d-flex --}}
            </div>
        </div>

        {{-- Chapters --}}
        @if($cerita->isi_cerita)
        @php
            $adsByChapter = $cerita->adPlacements
                ->filter(fn($placement) => $placement->ad && $placement->ad->status)
                ->groupBy('after_chapter');
        @endphp
        <h6 class="mb-4 text-muted text-uppercase" style="font-size:.75rem;letter-spacing:.08em;">Isi Cerita</h6>
        @foreach($cerita->isi_cerita as $key => $content)
        @php
            $chapterNumber = $loop->iteration;
            $isLocked = ($cerita->lock[$key] ?? false) === true;
        @endphp
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between py-3">
                <span class="fw-medium text-capitalize">{{ $key }}</span>
                @if($isLocked)
                    <span class="badge bg-label-warning"><i class="icon-base bx bx-lock me-1"></i> Terkunci</span>
                @else
                    <span class="badge bg-label-success"><i class="icon-base bx bx-lock-open me-1"></i> Bebas</span>
                @endif
            </div>
            <div class="card-body">
                <p class="mb-0" style="white-space: pre-line;">{{ $content }}</p>
            </div>
        </div>
        @foreach($adsByChapter->get($chapterNumber, collect()) as $placement)
        @php $ad = $placement->ad; @endphp
        <div class="card mb-4 border border-primary">
            <div class="card-header d-flex align-items-center justify-content-between py-3">
                <span class="fw-medium">Ads setelah Chapter {{ $chapterNumber }}</span>
                <span class="badge bg-label-{{ $ad->media_type === 'video' ? 'danger' : 'info' }}">{{ $ad->media_type === 'video' ? 'Video' : 'Gambar' }}</span>
            </div>
            <div class="card-body">
                @if($ad->target_url)
                    <a href="{{ $ad->target_url }}" target="_blank" rel="noopener" class="d-inline-block">
                @endif
                @if($ad->media_type === 'video')
                    <video src="{{ $ad->media_url }}" class="rounded w-100 bg-label-secondary" style="max-height:280px;object-fit:contain;" controls></video>
                @else
                    <img src="{{ $ad->media_url }}" alt="{{ $ad->title }}" class="rounded img-fluid" style="max-height:280px;" onerror="this.src='https://placehold.co/640x280'">
                @endif
                @if($ad->target_url)
                    </a>
                @endif
                <div class="mt-3 fw-medium">{{ $ad->title }}</div>
            </div>
        </div>
        @endforeach
        @endforeach
        @else
        <div class="alert alert-info">Belum ada isi cerita.</div>
        @endif

    </div>
</div>
@endsection
