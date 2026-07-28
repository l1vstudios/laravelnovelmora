@extends('layouts/contentNavbarLayout')
@section('title', 'Detail Pusat Bantuan')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">{{ $pusatBantuan->nama_layanan }}</h5>
                <a href="{{ route('pusat-bantuan.index') }}" class="btn btn-outline-secondary btn-sm"><i class="icon-base bx bx-arrow-back me-1"></i> Kembali</a>
            </div>
            <div class="card-body">
                <small class="text-muted d-block mb-2">Isi Layanan</small>
                <div class="text-break">{!! nl2br(e($pusatBantuan->isi_layanan)) !!}</div>
            </div>
        </div>
    </div>
</div>
@endsection
