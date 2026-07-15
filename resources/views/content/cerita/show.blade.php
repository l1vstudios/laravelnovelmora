@extends('layouts/contentNavbarLayout')
@section('title', 'Detail Cerita')

@section('content')
  <div class="row justify-content-center">
    <div class="col-md-10">
      <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="mb-0">Detail Cerita: {{ $cerita->judul }}</h5>
          <a href="{{ route('cerita.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="icon-base bx bx-arrow-back me-1"></i> Kembali
          </a>
        </div>
        <div class="card-body">
          <h6 class="mb-4 text-muted text-uppercase" style="font-size:.75rem;letter-spacing:.08em;">Informasi Dasar</h6>
          <div class="row g-4 mb-4">
            <div class="col-md-3">
              @if ($cerita->cover)
                <img src="{{ Storage::url($cerita->cover) }}" alt="{{ $cerita->judul }}"
                  class="rounded w-100 object-fit-cover" style="height: 220px;">
              @else
                <div class="bg-secondary rounded w-100 d-flex align-items-center justify-content-center"
                  style="height: 220px;">
                  <span class="text-white">No Cover</span>
                </div>
              @endif
            </div>
            <div class="col-md-9">
              <dl class="row mb-0">
                <dt class="col-sm-3">Judul</dt>
                <dd class="col-sm-9">{{ $cerita->judul }}</dd>

                <dt class="col-sm-3">Kategori</dt>
                <dd class="col-sm-9">{{ $cerita->kategori?->default_title ?? '-' }}</dd>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">
                  @if ($cerita->status)
                    <span class="badge bg-label-success">Aktif</span>
                  @else
                    <span class="badge bg-label-danger">Nonaktif</span>
                  @endif
                </dd>

                <dt class="col-sm-3">Sinopsis</dt>
                <dd class="col-sm-9">{{ $cerita->sinopsis ?: '-' }}</dd>
              </dl>
            </div>
          </div>

          <div class="d-flex align-items-center justify-content-between mb-4">
            <h6 class="mb-0 text-muted text-uppercase" style="font-size:.75rem;letter-spacing:.08em;">Ads Cerita</h6>
          </div>

          @if ($cerita->adPlacements->isNotEmpty())
            <div class="table-responsive text-nowrap mb-5">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Ads</th>
                    <th>Posisi</th>
                    <th>Chapter</th>
                    <th>Scope</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($cerita->adPlacements as $placement)
                    <tr>
                      <td>{{ $placement->ad->title ?? '-' }}</td>
                      <td>{{ ($placement->placement_position ?? 'after') === 'before' ? 'Sebelum' : 'Setelah' }}</td>
                      <td>Chapter {{ $placement->after_chapter }}</td>
                      <td>
                        @if ($placement->is_global)
                          <span class="badge bg-label-primary">Global</span>
                        @else
                          <span class="badge bg-label-secondary">Manual</span>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <p class="text-muted mb-5">Belum ada ads yang dipasang di cerita ini.</p>
          @endif

          <div class="d-flex align-items-center justify-content-between mb-4">
            <h6 class="mb-0 text-muted text-uppercase" style="font-size:.75rem;letter-spacing:.08em;">Isi Cerita
              ({{ $cerita->parts }} Chapters)</h6>
          </div>

          @if ($cerita->isi_cerita && count($cerita->isi_cerita))
            <div class="accordion" id="chaptersAccordion">
              @foreach ($cerita->isi_cerita as $key => $chapter)
                <div class="accordion-item">
                  <h2 class="accordion-header" id="heading{{ $loop->iteration }}">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                      data-bs-target="#collapse{{ $loop->iteration }}" aria-expanded="false"
                      aria-controls="collapse{{ $loop->iteration }}">
                      {{ $chapter['title'] ?? $key }}
                      @if (isset($cerita->lock[$key]) && $cerita->lock[$key])
                        <span class="badge bg-label-danger ms-2"><i class="icon-base bx bx-lock me-1"></i>
                          Locked</span>
                      @else
                        <span class="badge bg-label-success ms-2"><i class="icon-base bx bx-lock-open me-1"></i>
                          Open</span>
                      @endif
                    </button>
                  </h2>
                  <div id="collapse{{ $loop->iteration }}" class="accordion-collapse collapse"
                    aria-labelledby="heading{{ $loop->iteration }}" data-bs-parent="#chaptersAccordion">
                    <div class="accordion-body">
                      @php($chapterContent = $chapter['content'] ?? '')
                      @if ($chapterContent !== strip_tags($chapterContent))
                        {!! $chapterContent !!}
                      @else
                        {!! nl2br(e($chapterContent)) !!}
                      @endif
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <p class="text-muted">Belum ada chapter yang ditambahkan.</p>
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection
