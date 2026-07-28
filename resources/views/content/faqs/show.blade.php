@extends('layouts/contentNavbarLayout')
@section('title', 'Detail FAQ')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Detail FAQ</h5>
                <a href="{{ route('faqs.index') }}" class="btn btn-outline-secondary btn-sm"><i class="icon-base bx bx-arrow-back me-1"></i> Kembali</a>
            </div>
            <div class="card-body">
                <small class="text-muted d-block mb-1">Pertanyaan</small>
                <h5>{{ $faq->question }}</h5>
                <small class="text-muted d-block mt-5 mb-2">Jawaban</small>
                <div class="text-break">{!! nl2br(e($faq->answer)) !!}</div>
                <small class="text-muted d-block mt-5 mb-1">Status</small>
                @if($faq->status)<span class="badge bg-label-success">Aktif</span>@else<span class="badge bg-label-secondary">Nonaktif</span>@endif
            </div>
        </div>
    </div>
</div>
@endsection
