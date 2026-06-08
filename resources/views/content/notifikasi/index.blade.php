@extends('layouts/contentNavbarLayout')
@section('title', 'Notifikasi')

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible mb-6">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Master Notifikasi</h5>
                <a href="{{ route('notifikasi.create') }}" class="btn btn-primary"><i class="icon-base bx bx-plus me-1"></i> Tambah Notifikasi</a>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead><tr><th>#</th><th>Judul</th><th>Pesan</th><th>Dibuat</th><th>Aksi</th></tr></thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($notifikasis as $notif)
                        <tr>
                            <td>{{ $loop->iteration + ($notifikasis->currentPage() - 1) * $notifikasis->perPage() }}</td>
                            <td><span class="fw-medium">{{ $notif->title }}</span></td>
                            <td><small class="text-muted">{{ Str::limit($notif->message, 60) }}</small></td>
                            <td>{{ $notif->created_at ? $notif->created_at->format('d M Y') : '-' }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="icon-base bx bx-dots-vertical-rounded"></i></button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('notifikasi.show', $notif) }}"><i class="icon-base bx bx-show me-1"></i> Detail</a>
                                        <a class="dropdown-item" href="{{ route('notifikasi.edit', $notif) }}"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                                        <form action="{{ route('notifikasi.destroy', $notif) }}" method="POST" data-confirm="Hapus notifikasi ini?">
                                            @csrf @method('DELETE')
                                            <button class="dropdown-item text-danger"><i class="icon-base bx bx-trash me-1"></i> Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-6 text-muted">Belum ada notifikasi. <a href="{{ route('notifikasi.create') }}">Tambah sekarang</a>.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($notifikasis->hasPages())<div class="card-footer d-flex justify-content-end">{{ $notifikasis->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
