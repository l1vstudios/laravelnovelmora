@extends('layouts/contentNavbarLayout')
@section('title', 'Dashboard')
@section('content')
  <div class="d-flex align-items-center justify-content-between mb-6">
    <div>
      <h4 class="mb-1">Dashboard</h4>
      <small class="text-muted">Ringkasan data NovelMora</small>
    </div>
    <small class="text-muted">{{ now()->translatedFormat('d F Y') }}</small>
  </div>
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-4">
            <span class="badge bg-label-primary rounded p-2">
              <i class="icon-base bx bx-book-open icon-md"></i>
            </span>
            <a href="{{ route('cerita.index') }}" class="text-muted" style="font-size:.75rem;">Lihat semua →</a>
          </div>
          <h5 class="mb-1">{{ number_format($stats['cerita']) }}</h5>
          <small class="text-muted">Total Cerita</small>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-4">
            <span class="badge bg-label-info rounded p-2">
              <i class="icon-base bx bx-category icon-md"></i>
            </span>
            <a href="{{ route('kategori.index') }}" class="text-muted" style="font-size:.75rem;">Lihat semua →</a>
          </div>
          <h5 class="mb-1">{{ number_format($stats['kategori']) }}</h5>
          <small class="text-muted">Total Kategori</small>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-4">
            <span class="badge bg-label-success rounded p-2">
              <i class="icon-base bx bx-group icon-md"></i>
            </span>
            <a href="{{ route('pengguna.index') }}" class="text-muted" style="font-size:.75rem;">Lihat semua →</a>
          </div>
          <h5 class="mb-1">{{ number_format($stats['pengguna']) }}</h5>
          <small class="text-muted">Total Pengguna</small>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-4">
            <span class="badge bg-label-warning rounded p-2">
              <i class="icon-base bx bx-image-alt icon-md"></i>
            </span>
            <a href="{{ route('slider.index') }}" class="text-muted" style="font-size:.75rem;">Lihat semua →</a>
          </div>
          <h5 class="mb-1">{{ number_format($stats['slider']) }}</h5>
          <small class="text-muted">Total Slider</small>
        </div>
      </div>
    </div>
  </div>
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-4">
            <span class="badge bg-label-danger rounded p-2">
              <i class="icon-base bx bx-show icon-md"></i>
            </span>
          </div>
          <h5 class="mb-1">{{ number_format($stats['total_read']) }}</h5>
          <small class="text-muted">Total Dibaca</small>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-4">
            <span class="badge bg-label-secondary rounded p-2">
              <i class="icon-base bx bx-like icon-md"></i>
            </span>
          </div>
          <h5 class="mb-1">{{ number_format($stats['total_vote']) }}</h5>
          <small class="text-muted">Total Vote</small>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-4">
            <span class="badge bg-label-primary rounded p-2">
              <i class="icon-base bx bx-bell icon-md"></i>
            </span>
            <a href="{{ route('notifikasi.index') }}" class="text-muted" style="font-size:.75rem;">Lihat semua →</a>
          </div>
          <h5 class="mb-1">{{ number_format($stats['notifikasi']) }}</h5>
          <small class="text-muted">Total Notifikasi</small>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-4">
            <span class="badge bg-label-info rounded p-2">
              <i class="icon-base bx bx-code-block icon-md"></i>
            </span>
            <a href="{{ route('versi.index') }}" class="text-muted" style="font-size:.75rem;">Lihat semua →</a>
          </div>
          <h5 class="mb-1">{{ number_format($stats['versi']) }}</h5>
          <small class="text-muted">Versi Aplikasi</small>
        </div>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="mb-0">Cerita Terbaru</h5>
      <a href="{{ route('cerita.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
    </div>
    <div class="table-responsive text-nowrap">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Parts</th>
            <th>Dibaca</th>
            <th>Vote</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          @forelse($latestCeritas as $cerita)
            <tr>
              <td>
                <a href="{{ route('cerita.show', $cerita) }}" class="fw-medium text-body">
                  {{ $cerita->judul }}
                </a>
              </td>
              <td>{{ $cerita->kategori->default_title ?? '-' }}</td>
              <td><span class="badge bg-label-info">{{ $cerita->parts }}</span></td>
              <td>{{ number_format($cerita->total_read) }}</td>
              <td>{{ number_format($cerita->total_vote) }}</td>
              <td>
                @if ($cerita->status)
                  <span class="badge bg-label-success">Aktif</span>
                @else
                  <span class="badge bg-label-secondary">Nonaktif</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center py-6 text-muted">
                Belum ada cerita.
                <a href="{{ route('cerita.create') }}">Tambah sekarang</a>.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection
