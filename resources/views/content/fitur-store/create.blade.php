@extends('layouts/contentNavbarLayout')
@section('title', 'Tambah Fitur Store')

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const list = document.getElementById('feature-list');
    const addButton = document.getElementById('add-feature-row');

    function bindRemoveButtons() {
        list?.querySelectorAll('[data-remove-feature]').forEach((button) => {
            button.onclick = () => {
                if (list.querySelectorAll('[data-feature-row]').length > 1) {
                    button.closest('[data-feature-row]').remove();
                }
            };
        });
    }

    addButton?.addEventListener('click', () => {
        const row = list.querySelector('[data-feature-row]').cloneNode(true);
        row.querySelector('input').value = '';
        list.appendChild(row);
        bindRemoveButtons();
        row.querySelector('input').focus();
    });

    bindRemoveButtons();
});
</script>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Tambah Fitur Store</h5>
                <a href="{{ route('fitur-store.index') }}" class="btn btn-outline-secondary btn-sm"><i class="icon-base bx bx-arrow-back me-1"></i> Kembali</a>
            </div>
            <div class="card-body">
                @if($errors->any())
                <div class="alert alert-danger alert-dismissible mb-6"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif
                <form action="{{ route('fitur-store.store') }}" method="POST">
                    @csrf
                    <div class="mb-5">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label mb-0">Daftar Fitur <span class="text-danger">*</span></label>
                            <button type="button" id="add-feature-row" class="btn btn-sm btn-outline-primary">
                                <i class="icon-base bx bx-plus me-1"></i> Tambah Baris
                            </button>
                        </div>
                        <div id="feature-list" class="d-grid gap-2">
                            @foreach(old('features', $features) as $feature)
                                <div class="input-group" data-feature-row>
                                    <input type="text" name="features[]" class="form-control @error('features') is-invalid @enderror"
                                        value="{{ $feature }}" placeholder="Contoh: Akses semua bab" {{ $loop->first ? 'autofocus' : '' }}>
                                    <button type="button" class="btn btn-outline-danger" data-remove-feature>
                                        <i class="icon-base bx bx-trash"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                        @error('features')<div class="text-danger mt-1" style="font-size:.875em;">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-5">
                        <div class="form-check form-switch">
                            <input type="hidden" name="status" value="0">
                            <input class="form-check-input" type="checkbox" name="status" id="status" value="1" {{ old('status', $fiturStore?->status ?? '1') ? 'checked' : '' }}>
                            <label class="form-check-label" for="status">Status Aktif</label>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i> Simpan</button>
                        <a href="{{ route('fitur-store.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
