@extends('layouts/contentNavbarLayout')
@section('title', 'Pusat Bantuan')

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible mb-6">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Pusat Bantuan</h5>
                <a href="{{ route('pusat-bantuan.create') }}" class="btn btn-primary"><i class="icon-base bx bx-plus me-1"></i> Tambah Bantuan</a>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead><tr><th>#</th><th>Nama Layanan</th><th>Isi Layanan</th><th>Dibuat</th><th>Aksi</th></tr></thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($pusatBantuans as $item)
                        <tr>
                            <td>{{ $loop->iteration + ($pusatBantuans->currentPage() - 1) * $pusatBantuans->perPage() }}</td>
                            <td><span class="fw-medium">{{ $item->nama_layanan }}</span></td>
                            <td><small class="text-muted">{{ Str::limit($item->isi_layanan, 80) }}</small></td>
                            <td>{{ $item->created_at ? $item->created_at->format('d M Y') : '-' }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="icon-base bx bx-dots-vertical-rounded"></i></button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('pusat-bantuan.show', $item) }}"><i class="icon-base bx bx-show me-1"></i> Detail</a>
                                        <a class="dropdown-item" href="{{ route('pusat-bantuan.edit', $item) }}"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                                        <form action="{{ route('pusat-bantuan.destroy', $item) }}" method="POST" data-confirm="Hapus pusat bantuan ini?">
                                            @csrf @method('DELETE')
                                            <button class="dropdown-item text-danger"><i class="icon-base bx bx-trash me-1"></i> Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-6 text-muted">Belum ada pusat bantuan. <a href="{{ route('pusat-bantuan.create') }}">Tambah sekarang</a>.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($pusatBantuans->hasPages())<div class="card-footer d-flex justify-content-end">{{ $pusatBantuans->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
