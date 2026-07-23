@extends('layouts/contentNavbarLayout')
@section('title', 'Daftar Cerita')

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const scopeAll = document.getElementById('global-lock-all');
    const storySelect = document.getElementById('global-lock-stories');
    const globalLockModal = document.getElementById('global-lock-modal');
    const hasGlobalLockErrors = @json($errors->any());

    if (!scopeAll || !storySelect) {
        return;
    }

    function syncGlobalLockStories() {
        storySelect.disabled = scopeAll.checked;
        storySelect.required = !scopeAll.checked;

        if (scopeAll.checked) {
            Array.from(storySelect.options).forEach((option) => {
                option.selected = false;
            });
        }
    }

    scopeAll.addEventListener('change', syncGlobalLockStories);
    syncGlobalLockStories();

    if (hasGlobalLockErrors && globalLockModal && window.bootstrap) {
        window.bootstrap.Modal.getOrCreateInstance(globalLockModal).show();
    }
});
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-12">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible mb-6" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible mb-6" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
                <h5 class="mb-0">Daftar Cerita</h5>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#global-lock-modal">
                        <i class="icon-base bx bx-lock me-1"></i> Set Lock Global
                    </button>
                    <a href="{{ route('cerita.create') }}" class="btn btn-primary">
                        <i class="icon-base bx bx-plus me-1"></i> Tambah Cerita
                    </a>
                </div>
            </div>
            <div class="card-body border-bottom pb-4 pt-3">
                <form method="GET" action="{{ route('cerita.index') }}" class="row g-3 align-items-end">
                    <div class="col-auto">
                        <label class="form-label mb-1 text-muted" style="font-size:.75rem;">Status</label>
                        <select name="status" class="form-select form-select-sm" style="min-width:130px;">
                            <option value="">Semua</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label mb-1 text-muted" style="font-size:.75rem;">Rekomendasi</label>
                        <select name="recomendation" class="form-select form-select-sm" style="min-width:130px;">
                            <option value="">Semua</option>
                            <option value="1" {{ request('recomendation') === '1' ? 'selected' : '' }}>Ya</option>
                            <option value="0" {{ request('recomendation') === '0' ? 'selected' : '' }}>Tidak</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label mb-1 text-muted" style="font-size:.75rem;">Wajib Dibaca</label>
                        <select name="wajib_dibaca" class="form-select form-select-sm" style="min-width:130px;">
                            <option value="">Semua</option>
                            <option value="1" {{ request('wajib_dibaca') === '1' ? 'selected' : '' }}>Ya</option>
                            <option value="0" {{ request('wajib_dibaca') === '0' ? 'selected' : '' }}>Tidak</option>
                        </select>
                    </div>
                    <div class="col-auto d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="icon-base bx bx-filter me-1"></i> Filter</button>
                        @if(request()->hasAny(['status','recomendation','wajib_dibaca']))
                        <a href="{{ route('cerita.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cover</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Parts</th>
                            <th>Read</th>
                            <th>Vote</th>
                            <th>Status</th>
                            <th>Rekomendasi</th>
                            <th>Wajib Baca</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($ceritas as $cerita)
                        <tr>
                            <td>{{ $loop->iteration + ($ceritas->currentPage() - 1) * $ceritas->perPage() }}</td>
                            <td>
                                @if($cerita->cover)
                                    <img src="{{ asset('storage/' . $cerita->cover) }}" alt="{{ $cerita->judul }}"
                                        class="rounded" style="width:40px;height:54px;object-fit:cover;">
                                @else
                                    <div class="rounded bg-label-secondary d-flex align-items-center justify-content-center"
                                        style="width:40px;height:54px;">
                                        <i class="icon-base bx bx-image text-muted" style="font-size:1.1rem;"></i>
                                    </div>
                                @endif
                            </td>
                            <td><span class="fw-medium">{{ $cerita->judul }}</span></td>
                            <td>{{ $cerita->kategori->default_title ?? '-' }}</td>
                            <td><span class="badge bg-label-info">{{ $cerita->parts }} chapter</span></td>
                            <td>{{ number_format($cerita->total_read) }}</td>
                            <td>{{ number_format($cerita->total_vote) }}</td>
                            <td>
                                @if($cerita->status)
                                    <span class="badge bg-label-success">Aktif</span>
                                @else
                                    <span class="badge bg-label-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                @if($cerita->recomendation)
                                    <i class="icon-base bx bx-check-circle text-success"></i>
                                @else
                                    <i class="icon-base bx bx-x-circle text-secondary"></i>
                                @endif
                            </td>
                            <td>
                                @if($cerita->wajib_dibaca)
                                    <i class="icon-base bx bx-check-circle text-success"></i>
                                @else
                                    <i class="icon-base bx bx-x-circle text-secondary"></i>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="icon-base bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('cerita.show', $cerita) }}">
                                            <i class="icon-base bx bx-show me-1"></i> Detail
                                        </a>
                                        <a class="dropdown-item" href="{{ route('cerita.edit', $cerita) }}">
                                            <i class="icon-base bx bx-edit-alt me-1"></i> Edit
                                        </a>
                                        <form action="{{ route('cerita.destroy', $cerita) }}" method="POST"
                                            data-confirm="Hapus cerita ini?">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="icon-base bx bx-trash me-1"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-6 text-muted">
                                Belum ada cerita. <a href="{{ route('cerita.create') }}">Tambah sekarang</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($ceritas->hasPages())
            <div class="card-footer d-flex justify-content-end">
                {{ $ceritas->links() }}
            </div>
            @endif
        </div>

    </div>
</div>

<div class="modal fade" id="global-lock-modal" tabindex="-1" aria-labelledby="global-lock-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="global-lock-modal-title">Set Lock Global</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                @if($lockCeritas->isEmpty())
                    <div class="alert alert-info mb-0">Belum ada cerita untuk di-lock.</div>
                @else
                    <form method="POST" action="{{ route('cerita.global-lock') }}" id="global-lock-form" class="row g-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="lock_scope" value="selected">

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="lock_scope" value="all"
                                    id="global-lock-all" {{ old('lock_scope') === 'all' ? 'checked' : '' }}>
                                <label class="form-check-label" for="global-lock-all">Pilih semua judul</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="global-lock-stories" class="form-label">Judul Novel</label>
                            <select id="global-lock-stories" name="cerita_ids[]" class="form-select" multiple size="7">
                                @foreach($lockCeritas as $lockCerita)
                                    @php
                                        $chapterTotal = max((int) $lockCerita->parts, count($lockCerita->isi_cerita ?? []));
                                    @endphp
                                    <option value="{{ $lockCerita->id }}" {{ collect(old('cerita_ids', []))->contains($lockCerita->id) ? 'selected' : '' }}>
                                        {{ $lockCerita->judul }} ({{ $chapterTotal }} chapter)
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Tahan Ctrl atau Cmd untuk memilih lebih dari satu judul.</small>
                        </div>

                        <div class="col-md-6">
                            <label for="global-lock-start" class="form-label">Chapter Awal</label>
                            <input type="number" min="1" name="chapter_start" id="global-lock-start"
                                class="form-control" value="{{ old('chapter_start', 1) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label for="global-lock-end" class="form-label">Chapter Akhir</label>
                            <input type="number" min="1" name="chapter_end" id="global-lock-end"
                                class="form-control" value="{{ old('chapter_end', 5) }}" required>
                        </div>
                    </form>
                @endif
                {{-- //ucok --}}
            </div>
            @if($lockCeritas->isNotEmpty())
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="global-lock-form" class="btn btn-primary">
                        <i class="icon-base bx bx-lock me-1"></i> Lock
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
