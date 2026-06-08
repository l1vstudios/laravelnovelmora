@extends('layouts/contentNavbarLayout')
@section('title', 'Detail Kategori')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-6">
            <div>
                <h4 class="mb-1 text-capitalize">{{ $kategori->default_title }}</h4>
                <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('kategori.index') }}">Kategori</a></li>
                    <li class="breadcrumb-item active">{{ $kategori->default_title }}</li>
                </ol></nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('kategori.edit', $kategori) }}" class="btn btn-primary"><i class="icon-base bx bx-edit-alt me-1"></i> Edit</a>
                <form action="{{ route('kategori.destroy', $kategori) }}" method="POST" data-confirm="Hapus kategori ini?">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger"><i class="icon-base bx bx-trash"></i></button>
                </form>
                <a href="{{ route('kategori.index') }}" class="btn btn-outline-secondary"><i class="icon-base bx bx-arrow-back me-1"></i> Kembali</a>
            </div>
        </div>

        <div class="card mb-6">
            <div class="card-header"><h6 class="mb-0">Informasi Kategori</h6></div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-sm-4"><small class="text-muted d-block mb-1">Nama</small><span class="fw-medium text-capitalize">{{ $kategori->default_title }}</span></div>
                    <div class="col-sm-4"><small class="text-muted d-block mb-1">Jumlah Cerita</small><span class="badge bg-label-info">{{ $kategori->ceritas->count() }} cerita</span></div>
                    <div class="col-sm-4"><small class="text-muted d-block mb-1">Dibuat</small><span class="fw-medium">{{ $kategori->created_at ? $kategori->created_at->format('d M Y') : '-' }}</span></div>
                </div>
            </div>
        </div>

        @if($kategori->ceritas->count())
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Cerita dalam Kategori Ini</h6></div>
            <div class="table-responsive">
                <table class="table"><thead><tr><th>#</th><th>Judul</th><th>Parts</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($kategori->ceritas as $c)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><a href="{{ route('cerita.show', $c) }}">{{ $c->judul }}</a></td>
                        <td>{{ $c->parts }}</td>
                        <td>@if($c->status)<span class="badge bg-label-success">Aktif</span>@else<span class="badge bg-label-secondary">Nonaktif</span>@endif</td>
                    </tr>
                    @endforeach
                </tbody></table>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
