@extends('layouts/contentNavbarLayout')
@section('title', 'Slider')

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible mb-6">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Master Slider</h5>
                <a href="{{ route('slider.create') }}" class="btn btn-primary"><i class="icon-base bx bx-plus me-1"></i> Tambah Slider</a>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead><tr><th>#</th><th>Preview</th><th>URL Gambar</th><th>Status</th><th>Dibuat</th><th>Aksi</th></tr></thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($sliders as $slider)
                        <tr>
                            <td>{{ $loop->iteration + ($sliders->currentPage() - 1) * $sliders->perPage() }}</td>
                            <td><img src="{{ $slider->image_url }}" alt="Slider" class="rounded" style="width:80px;height:50px;object-fit:cover;" onerror="this.src='https://placehold.co/80x50'"></td>
                            <td><small class="text-muted">{{ Str::limit($slider->image_url, 50) }}</small></td>
                            <td>@if($slider->status)<span class="badge bg-label-success">Aktif</span>@else<span class="badge bg-label-secondary">Nonaktif</span>@endif</td>
                            <td>{{ $slider->created_at ? $slider->created_at->format('d M Y') : '-' }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="icon-base bx bx-dots-vertical-rounded"></i></button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('slider.show', $slider) }}"><i class="icon-base bx bx-show me-1"></i> Detail</a>
                                        <a class="dropdown-item" href="{{ route('slider.edit', $slider) }}"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                                        <form action="{{ route('slider.destroy', $slider) }}" method="POST" data-confirm="Hapus slider ini?">
                                            @csrf @method('DELETE')
                                            <button class="dropdown-item text-danger"><i class="icon-base bx bx-trash me-1"></i> Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-6 text-muted">Belum ada slider. <a href="{{ route('slider.create') }}">Tambah sekarang</a>.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($sliders->hasPages())<div class="card-footer d-flex justify-content-end">{{ $sliders->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
