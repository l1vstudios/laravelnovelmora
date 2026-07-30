@extends('layouts/contentNavbarLayout')
@section('title', 'Fitur Store')

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible mb-6">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Fitur Store</h5>
                <a href="{{ route('fitur-store.create') }}" class="btn btn-primary"><i class="icon-base bx bx-plus me-1"></i> Tambah Fitur</a>
            </div>
            <div class="card-body border-bottom pb-4 pt-3">
                <form method="GET" action="{{ route('fitur-store.index') }}" class="row g-3 align-items-end">
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
                        @if(request()->filled('status'))<a href="{{ route('fitur-store.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>@endif
                    </div>
                </form>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead><tr><th>#</th><th data-sortable="false">Judul</th><th data-sortable="false">Deskripsi</th><th>Status</th><th>Dibuat</th><th>Aksi</th></tr></thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($fiturStores as $item)
                        <tr>
                            <td>{{ $loop->iteration + ($fiturStores->currentPage() - 1) * $fiturStores->perPage() }}</td>
                            <td><span class="fw-medium">{{ data_get($item->konten, 'title', '-') }}</span></td>
                            <td><small class="text-muted">{{ Str::limit(data_get($item->konten, 'description', '-'), 100) }}</small></td>
                            <td>@if($item->status)<span class="badge bg-label-success">Aktif</span>@else<span class="badge bg-label-secondary">Nonaktif</span>@endif</td>
                            <td>{{ $item->created_at ? $item->created_at->format('d M Y') : '-' }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="icon-base bx bx-dots-vertical-rounded"></i></button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('fitur-store.show', $item) }}"><i class="icon-base bx bx-show me-1"></i> Detail</a>
                                        <a class="dropdown-item" href="{{ route('fitur-store.edit', $item) }}"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                                        <form action="{{ route('fitur-store.destroy', $item) }}" method="POST" data-confirm="Hapus fitur store ini?">
                                            @csrf @method('DELETE')
                                            <button class="dropdown-item text-danger"><i class="icon-base bx bx-trash me-1"></i> Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-6 text-muted">Belum ada fitur store. <a href="{{ route('fitur-store.create') }}">Tambah sekarang</a>.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($fiturStores->hasPages())<div class="card-footer d-flex justify-content-end">{{ $fiturStores->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
