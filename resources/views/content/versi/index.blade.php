@extends('layouts/contentNavbarLayout')
@section('title', 'Versi Aplikasi')

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible mb-6">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Versi Aplikasi</h5>
                <a href="{{ route('versi.create') }}" class="btn btn-primary"><i class="icon-base bx bx-plus me-1"></i> Tambah Versi</a>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead><tr><th>#</th><th>Nama Versi</th><th>Kode Versi</th><th>Dibuat</th><th>Aksi</th></tr></thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($versis as $versi)
                        <tr>
                            <td>{{ $loop->iteration + ($versis->currentPage() - 1) * $versis->perPage() }}</td>
                            <td><span class="fw-medium">{{ $versi->version_name }}</span></td>
                            <td><span class="badge bg-label-primary">{{ $versi->version_code }}</span></td>
                            <td>{{ $versi->created_at ? $versi->created_at->format('d M Y') : '-' }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="icon-base bx bx-dots-vertical-rounded"></i></button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('versi.edit', $versi) }}"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                                        <form action="{{ route('versi.destroy', $versi) }}" method="POST" data-confirm="Hapus versi ini?">
                                            @csrf @method('DELETE')
                                            <button class="dropdown-item text-danger"><i class="icon-base bx bx-trash me-1"></i> Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-6 text-muted">Belum ada versi. <a href="{{ route('versi.create') }}">Tambah sekarang</a>.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($versis->hasPages())<div class="card-footer d-flex justify-content-end">{{ $versis->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
