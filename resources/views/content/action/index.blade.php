@extends('layouts/contentNavbarLayout')
@section('title', 'Master Action')

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible mb-6">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Master Action</h5>
                <a href="{{ route('action.create') }}" class="btn btn-primary"><i class="icon-base bx bx-plus me-1"></i> Tambah Action</a>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead><tr><th>#</th><th>Nama Action</th><th>Dibuat</th><th>Aksi</th></tr></thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($actions as $action)
                        <tr>
                            <td>{{ $loop->iteration + ($actions->currentPage() - 1) * $actions->perPage() }}</td>
                            <td><span class="fw-medium">{{ $action->action_name }}</span></td>
                            <td>{{ $action->created_at ? $action->created_at->format('d M Y') : '-' }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="icon-base bx bx-dots-vertical-rounded"></i></button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('action.edit', $action) }}"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                                        <form action="{{ route('action.destroy', $action) }}" method="POST" data-confirm="Hapus action ini?">
                                            @csrf @method('DELETE')
                                            <button class="dropdown-item text-danger"><i class="icon-base bx bx-trash me-1"></i> Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-6 text-muted">Belum ada action. <a href="{{ route('action.create') }}">Tambah sekarang</a>.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($actions->hasPages())<div class="card-footer d-flex justify-content-end">{{ $actions->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
