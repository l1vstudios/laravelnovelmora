@extends('layouts/contentNavbarLayout')
@section('title', 'Tambah Reward Harian')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Tambah Reward Harian</h5>
                <a href="{{ route('daily-rewards.index') }}" class="btn btn-outline-secondary btn-sm"><i class="icon-base bx bx-arrow-back me-1"></i> Kembali</a>
            </div>
            <div class="card-body">
                @if($errors->any())<div class="alert alert-danger alert-dismissible mb-6"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
                <form action="{{ route('daily-rewards.store') }}" method="POST">
                    @csrf
                    @include('content.daily-rewards._form', ['dailyReward' => null])
                    <div class="d-flex gap-3 mt-6">
                        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i> Simpan</button>
                        <a href="{{ route('daily-rewards.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
