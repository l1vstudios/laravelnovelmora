@extends('layouts/contentNavbarLayout')
@section('title', 'Kategori')

@section('content')
  <div class="row">
    <div class="col-12">
      @if (session('success'))
        <div class="alert alert-success alert-dismissible mb-6" role="alert">
          {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="mb-0">Master Kategori</h5>
          <a href="{{ route('kategori.create') }}" class="btn btn-primary">
            <i class="icon-base bx bx-plus me-1"></i> Tambah Kategori
          </a>
        </div>
        <div class="card-body border-bottom pb-4 pt-3">
          <form method="GET" action="{{ route('kategori.index') }}" class="row g-3 align-items-end">
            <div class="col-auto">
              <label class="form-label mb-1 text-muted" style="font-size:.75rem;">Urutkan Cerita</label>
              <select name="sort" class="form-select form-select-sm" style="min-width:160px;">
                <option value="">Terbaru</option>
                <option value="terbanyak" {{ request('sort') === 'terbanyak' ? 'selected' : '' }}>Cerita Terbanyak
                </option>
                <option value="terkecil" {{ request('sort') === 'terkecil' ? 'selected' : '' }}>Cerita Terkecil</option>
              </select>
            </div>
            <div class="col-auto d-flex gap-2">
              <button type="submit" class="btn btn-sm btn-primary"><i class="icon-base bx bx-filter me-1"></i>
                Filter</button>
              @if (request()->filled('sort'))
                <a href="{{ route('kategori.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
              @endif
            </div>
          </form>
        </div>
        <div class="table-responsive text-nowrap">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>#</th>
                <th>Nama Kategori</th>
                <th>Jumlah Cerita</th>
                <th>Dibuat</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody class="table-border-bottom-0">
              @forelse($kategoris as $kat)
                <tr>
                  <td>{{ $loop->iteration + ($kategoris->currentPage() - 1) * $kategoris->perPage() }}</td>
                  <td><span class="fw-medium text-capitalize">{{ $kat->default_title }}</span></td>
                  <td><span class="badge bg-label-info">{{ $kat->ceritas_count }} cerita</span></td>
                  <td>{{ $kat->created_at ? $kat->created_at->format('d M Y') : '-' }}</td>
                  <td>
                    <div class="dropdown">
                      <button class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="icon-base bx bx-dots-vertical-rounded"></i>
                      </button>
                      <div class="dropdown-menu">
                        <a class="dropdown-item" href="{{ route('kategori.show', $kat) }}"><i
                            class="icon-base bx bx-show me-1"></i> Detail</a>
                        <a class="dropdown-item" href="{{ route('kategori.edit', $kat) }}"><i
                            class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                        <form action="{{ route('kategori.destroy', $kat) }}" method="POST"
                          data-confirm="Hapus kategori ini?">
                          @csrf @method('DELETE')
                          <button class="dropdown-item text-danger"><i class="icon-base bx bx-trash me-1"></i>
                            Hapus</button>
                        </form>
                      </div>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center py-6 text-muted">Belum ada kategori. <a
                      href="{{ route('kategori.create') }}">Tambah sekarang</a>.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($kategoris->hasPages())
          <div class="card-footer d-flex justify-content-end">{{ $kategoris->links() }}</div>
        @endif
      </div>
    </div>
  </div>
@endsection
