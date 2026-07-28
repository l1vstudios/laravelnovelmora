@extends('layouts/contentNavbarLayout')
@section('title', 'Syarat Ketentuan')

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible mb-6">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Syarat Ketentuan</h5>
                <a href="{{ route('syarat-ketentuan.create') }}" class="btn btn-primary"><i class="icon-base bx bx-plus me-1"></i> Tambah Syarat</a>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead><tr><th>#</th><th>Konten</th><th>Aksi</th></tr></thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($syaratKetentuans as $item)
                        <tr>
                            <td>{{ $loop->iteration + ($syaratKetentuans->currentPage() - 1) * $syaratKetentuans->perPage() }}</td>
                            <td><small class="text-muted">{{ Str::limit($item->konten, 120) }}</small></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="icon-base bx bx-dots-vertical-rounded"></i></button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('syarat-ketentuan.show', $item) }}"><i class="icon-base bx bx-show me-1"></i> Detail</a>
                                        <a class="dropdown-item" href="{{ route('syarat-ketentuan.edit', $item) }}"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                                        <form action="{{ route('syarat-ketentuan.destroy', $item) }}" method="POST" data-confirm="Hapus syarat ketentuan ini?">
                                            @csrf @method('DELETE')
                                            <button class="dropdown-item text-danger"><i class="icon-base bx bx-trash me-1"></i> Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center py-6 text-muted">Belum ada syarat ketentuan. <a href="{{ route('syarat-ketentuan.create') }}">Tambah sekarang</a>.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($syaratKetentuans->hasPages())<div class="card-footer d-flex justify-content-end">{{ $syaratKetentuans->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
