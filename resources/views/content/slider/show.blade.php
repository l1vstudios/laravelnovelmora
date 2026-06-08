@extends('layouts/contentNavbarLayout')
@section('title', 'Detail Slider')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="d-flex align-items-center justify-content-between mb-6">
            <h4 class="mb-0">Detail Slider</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('slider.edit', $slider) }}" class="btn btn-primary"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                <form action="{{ route('slider.destroy', $slider) }}" method="POST" data-confirm="Hapus slider ini?">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger"><i class="icon-base bx bx-trash"></i></button>
                </form>
                <a href="{{ route('slider.index') }}" class="btn btn-outline-secondary"><i class="icon-base bx bx-arrow-back me-1"></i> Kembali</a>
            </div>
        </div>
        <div class="card mb-6">
            <div class="card-body text-center">
                <img src="{{ $slider->image_url }}" alt="Slider" class="img-fluid rounded mb-4" style="max-height:300px;">
                <div class="mb-2">
                    @if($slider->status)<span class="badge bg-label-success">Aktif</span>@else<span class="badge bg-label-secondary">Nonaktif</span>@endif
                </div>
                <small class="text-muted d-block">{{ $slider->image_url }}</small>
                <small class="text-muted">Dibuat: {{ $slider->created_at ? $slider->created_at->format('d M Y') : '-' }}</small>
            </div>
        </div>
    </div>
</div>
@endsection
