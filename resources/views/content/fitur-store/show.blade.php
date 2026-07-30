@extends('layouts/contentNavbarLayout')
@section('title', 'Detail Fitur Store')

@php
    $konten = $fiturStore->konten ?? [];
@endphp

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Detail Fitur Store</h5>
                <a href="{{ route('fitur-store.index') }}" class="btn btn-outline-secondary btn-sm"><i class="icon-base bx bx-arrow-back me-1"></i> Kembali</a>
            </div>
            <div class="card-body">
                <small class="text-muted d-block mb-1">Status</small>
                @if($fiturStore->status)<span class="badge bg-label-success">Aktif</span>@else<span class="badge bg-label-secondary">Nonaktif</span>@endif
                <small class="text-muted d-block mt-5 mb-1">Judul</small>
                <div class="fw-medium">{{ data_get($konten, 'title', '-') }}</div>
                <small class="text-muted d-block mt-5 mb-1">Deskripsi</small>
                <div class="text-break">{!! nl2br(e(data_get($konten, 'description', '-'))) !!}</div>
            </div>
        </div>
    </div>
</div>
@endsection
