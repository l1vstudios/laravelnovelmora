@extends('layouts/contentNavbarLayout')
@section('title', 'Detail Syarat Ketentuan')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">{{ $syaratKetentuan->nama }}</h5>
                <a href="{{ route('syarat-ketentuan.index') }}" class="btn btn-outline-secondary btn-sm"><i class="icon-base bx bx-arrow-back me-1"></i> Kembali</a>
            </div>
            <div class="card-body">
                <small class="text-muted d-block mb-2">Isi Konten</small>
                <div class="text-break">{!! nl2br(e($syaratKetentuan->isi_konten)) !!}</div>
            </div>
        </div>
    </div>
</div>
@endsection
