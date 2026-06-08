@extends('layouts/contentNavbarLayout')
@section('title', 'Analitik Cerita')
@section('vendor-style')
  @vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection
@section('vendor-script')
  @vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection
@section('content')
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h4 class="mb-1">Analitik Cerita</h4>
      <small class="text-muted">Klik chart untuk melihat detail data</small>
    </div>
  </div>
  <div class="card mb-6">
    <div class="card-body py-4">
      <form method="GET" action="{{ route('analytics') }}" class="row g-3 align-items-end">
        <div class="col-sm-6 col-md-2">
          <label class="form-label mb-1">Tahun</label>
          <select name="year" class="form-select form-select-sm">
            <option value="">Semua Tahun</option>
            @foreach ($availableYears as $y)
              <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-sm-6 col-md-2">
          <label class="form-label mb-1">Bulan</label>
          <select name="month" class="form-select form-select-sm">
            <option value="">Semua Bulan</option>
            @foreach (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $i => $m)
              <option value="{{ $i + 1 }}" {{ $month == $i + 1 ? 'selected' : '' }}>{{ $m }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-sm-6 col-md-2">
          <label class="form-label mb-1">Minggu ke-</label>
          <select name="week" class="form-select form-select-sm">
            <option value="">Semua Minggu</option>
            @for ($w = 1; $w <= 52; $w++)
              <option value="{{ $w }}" {{ $week == $w ? 'selected' : '' }}>Minggu {{ $w }}</option>
            @endfor
          </select>
        </div>
        <div class="col-sm-6 col-md-2">
          <label class="form-label mb-1">Urutkan</label>
          <select name="sort_by" class="form-select form-select-sm">
            <option value="total_read" {{ $sortBy === 'total_read' ? 'selected' : '' }}>Total Dibaca</option>
            <option value="total_vote" {{ $sortBy === 'total_vote' ? 'selected' : '' }}>Total Vote</option>
            <option value="total_share" {{ $sortBy === 'total_share' ? 'selected' : '' }}>Total Share</option>
            <option value="engagement" {{ $sortBy === 'engagement' ? 'selected' : '' }}>Engagement Rate</option>
          </select>
        </div>
        <div class="col-sm-6 col-md-2">
          <label class="form-label mb-1">Arah</label>
          <select name="sort_dir" class="form-select form-select-sm">
            <option value="desc" {{ $sortDir === 'desc' ? 'selected' : '' }}>Tertinggi ↓</option>
            <option value="asc" {{ $sortDir === 'asc' ? 'selected' : '' }}>Terendah ↑</option>
          </select>
        </div>
        <div class="col-sm-6 col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-primary btn-sm flex-fill">
            <i class="icon-base bx bx-filter me-1"></i> Filter
          </button>
          <a href="{{ route('analytics') }}" class="btn btn-outline-secondary btn-sm">
            <i class="icon-base bx bx-reset"></i>
          </a>
        </div>
      </form>
    </div>
  </div>
  @if ($year || $month || $week)
    <div class="alert alert-info py-2 px-4 mb-6 d-flex align-items-center gap-3">
      <i class="icon-base bx bx-filter"></i>
      <span>
        Filter aktif:
        @if ($year)
          <strong>Tahun {{ $year }}</strong>
        @endif
        @if ($month)
          <strong>Bulan
            {{ ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][$month] }}</strong>
        @endif
        @if ($week)
          <strong>Minggu {{ $week }}</strong>
        @endif
        — Urutkan
        <strong>{{ $sortBy === 'total_read' ? 'Total Dibaca' : ($sortBy === 'total_vote' ? 'Total Vote' : ($sortBy === 'total_share' ? 'Total Share' : 'Engagement Rate')) }}</strong>
        <strong>{{ $sortDir === 'desc' ? 'Tertinggi' : 'Terendah' }}</strong>
      </span>
    </div>
  @endif
  <div class="row g-6 mb-6">
    @php
      $cards = [
          [
              'label' => 'Total Cerita',
              'value' => number_format($summary['total_cerita']),
              'icon' => 'bx-book-open',
              'color' => 'primary',
          ],
          [
              'label' => 'Total Dibaca',
              'value' => number_format($summary['total_read']),
              'icon' => 'bx-show',
              'color' => 'success',
          ],
          [
              'label' => 'Total Vote',
              'value' => number_format($summary['total_vote']),
              'icon' => 'bx-like',
              'color' => 'info',
          ],
          [
              'label' => 'Total Share',
              'value' => number_format($summary['total_share']),
              'icon' => 'bx-share-alt',
              'color' => 'warning',
          ],
          ['label' => 'Rata-rata Part', 'value' => $summary['avg_parts'], 'icon' => 'bx-layer', 'color' => 'danger'],
          [
              'label' => 'Wajib Dibaca',
              'value' => number_format($summary['wajib_dibaca']),
              'icon' => 'bx-star',
              'color' => 'secondary',
          ],
      ];
    @endphp
    @foreach ($cards as $c)
      <div class="col-sm-6 col-xl-2">
        <div class="card text-center h-100">
          <div class="card-body py-4">
            <span class="badge bg-label-{{ $c['color'] }} rounded p-2 mb-3">
              <i class="icon-base bx {{ $c['icon'] }} icon-md"></i>
            </span>
            <h5 class="mb-0">{{ $c['value'] }}</h5>
            <small class="text-muted">{{ $c['label'] }}</small>
          </div>
        </div>
      </div>
    @endforeach
  </div>
  <div class="row g-6 mb-6">
    <div class="col-xl-6">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="mb-0">Top 10 — Total Dibaca <span
              class="badge bg-label-secondary ms-1">{{ $sortDir === 'desc' ? '↓' : '↑' }}</span></h5>
          <small class="text-muted">Klik bar untuk lihat detail</small>
        </div>
        <div class="card-body">
          <div id="chartTopRead"></div>
        </div>
      </div>
    </div>
    <div class="col-xl-6">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="mb-0">Top 10 — Total Vote <span
              class="badge bg-label-secondary ms-1">{{ $sortDir === 'desc' ? '↓' : '↑' }}</span></h5>
          <small class="text-muted">Klik bar untuk lihat detail</small>
        </div>
        <div class="card-body">
          <div id="chartTopVote"></div>
        </div>
      </div>
    </div>
  </div>
  <div class="row g-6 mb-6">
    <div class="col-xl-4">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="mb-0">Distribusi Kategori</h5>
          <small class="text-muted">Klik slice untuk lihat cerita</small>
        </div>
        <div class="card-body d-flex align-items-center justify-content-center">
          <div id="chartKategori" style="width:100%"></div>
        </div>
      </div>
    </div>
    <div class="col-xl-4">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="mb-0">Status Publikasi</h5>
          <small class="text-muted">Klik slice untuk lihat cerita</small>
        </div>
        <div class="card-body d-flex align-items-center justify-content-center">
          <div id="chartStatus" style="width:100%"></div>
        </div>
      </div>
    </div>
    <div class="col-xl-4">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="mb-0">Rekomendasi</h5>
          <small class="text-muted">Klik slice untuk lihat cerita</small>
        </div>
        <div class="card-body d-flex align-items-center justify-content-center">
          <div id="chartRekomen" style="width:100%"></div>
        </div>
      </div>
    </div>
  </div>
  @php
    $baseQuery = http_build_query(array_filter(['year' => $year, 'month' => $month, 'week' => $week]));
    function sortUrl($col, $currentSortBy, $currentSortDir, $base)
    {
        $dir = $currentSortBy === $col && $currentSortDir === 'desc' ? 'asc' : 'desc';
        return '?' . ($base ? $base . '&' : '') . 'sort_by=' . $col . '&sort_dir=' . $dir;
    }
    function sortIcon($col, $currentSortBy, $currentSortDir)
    {
        if ($currentSortBy !== $col) {
            return '<span class="text-muted ms-1" style="font-size:.7rem">↕</span>';
        }
        return $currentSortDir === 'desc'
            ? '<span class="text-primary ms-1" style="font-size:.7rem">↓</span>'
            : '<span class="text-primary ms-1" style="font-size:.7rem">↑</span>';
    }
  @endphp
  <div class="card">
    <div class="card-header">
      <h5 class="mb-0">Detail Performa Cerita</h5>
    </div>
    <div class="table-responsive text-nowrap">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>
            <th>Judul</th>
            <th><a href="{{ sortUrl('total_read', $sortBy, $sortDir, $baseQuery) }}"
                class="text-body text-decoration-none">Dibaca{!! sortIcon('total_read', $sortBy, $sortDir) !!}</a></th>
            <th><a href="{{ sortUrl('total_vote', $sortBy, $sortDir, $baseQuery) }}"
                class="text-body text-decoration-none">Vote{!! sortIcon('total_vote', $sortBy, $sortDir) !!}</a></th>
            <th><a href="{{ sortUrl('total_share', $sortBy, $sortDir, $baseQuery) }}"
                class="text-body text-decoration-none">Share{!! sortIcon('total_share', $sortBy, $sortDir) !!}</a></th>
            <th><a href="{{ sortUrl('engagement', $sortBy, $sortDir, $baseQuery) }}"
                class="text-body text-decoration-none">Engagement Rate{!! sortIcon('engagement', $sortBy, $sortDir) !!}</a></th>
            <th></th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          @foreach ($detailCerita as $i => $c)
            @php $eng = $c->total_read > 0 ? round(($c->total_vote / $c->total_read) * 100, 1) : 0; @endphp
            <tr>
              <td>{{ ($detailCerita->currentPage() - 1) * $detailCerita->perPage() + $i + 1 }}</td>
              <td><span class="fw-medium">{{ $c->judul }}</span></td>
              <td>{{ number_format($c->total_read) }}</td>
              <td>{{ number_format($c->total_vote) }}</td>
              <td>{{ number_format($c->total_share) }}</td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="progress w-px-100" style="height:6px">
                    <div class="progress-bar bg-primary" style="width:{{ min($eng, 100) }}%"></div>
                  </div>
                  <small>{{ $eng }}%</small>
                </div>
              </td>
              <td><a href="{{ route('cerita.show', $c) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @if ($detailCerita->hasPages())
      <div class="card-footer d-flex justify-content-between align-items-center py-3">
        <small class="text-muted">
          Menampilkan {{ $detailCerita->firstItem() }}–{{ $detailCerita->lastItem() }} dari
          {{ $detailCerita->total() }} cerita
        </small>
        {{ $detailCerita->links() }}
      </div>
    @endif
  </div>
  <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="detailModalTitle">Detail</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead>
                <tr>
                  <th>
                  <th>Judul</th>
                  <th>Part</th>
                  <th>Dibaca</th>
                  <th>Vote</th>
                  <th></th>
                </tr>
              </thead>
              <tbody id="detailModalBody"></tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>
@endsection
@php
  $jsTopRead = $topRead
      ->map(function ($c) {
          return [
              'id' => $c->id,
              'judul' => $c->judul,
              'total_read' => $c->total_read,
              'total_vote' => $c->total_vote,
              'total_share' => $c->total_share,
              'parts' => $c->parts,
          ];
      })
      ->values()
      ->toArray();
  $jsTopVote = $topVote
      ->map(function ($c) {
          return [
              'id' => $c->id,
              'judul' => $c->judul,
              'total_read' => $c->total_read,
              'total_vote' => $c->total_vote,
              'total_share' => $c->total_share,
              'parts' => $c->parts,
          ];
      })
      ->values()
      ->toArray();
  $jsKategori = $byKategori
      ->map(function ($k) {
          return [
              'label' => $k->default_title,
              'ceritas' => $k->ceritas
                  ->map(function ($c) {
                      return [
                          'id' => $c->id,
                          'judul' => $c->judul,
                          'total_read' => $c->total_read,
                          'total_vote' => $c->total_vote,
                          'parts' => $c->parts,
                      ];
                  })
                  ->values()
                  ->toArray(),
          ];
      })
      ->values()
      ->toArray();
  $jsAktif = $ceritaAktif
      ->map(function ($c) {
          return [
              'id' => $c->id,
              'judul' => $c->judul,
              'total_read' => $c->total_read,
              'total_vote' => $c->total_vote,
              'parts' => $c->parts,
          ];
      })
      ->values()
      ->toArray();
  $jsNonaktif = $ceritaNonaktif
      ->map(function ($c) {
          return [
              'id' => $c->id,
              'judul' => $c->judul,
              'total_read' => $c->total_read,
              'total_vote' => $c->total_vote,
              'parts' => $c->parts,
          ];
      })
      ->values()
      ->toArray();
  $jsRekomen = $ceritaRekomen
      ->map(function ($c) {
          return [
              'id' => $c->id,
              'judul' => $c->judul,
              'total_read' => $c->total_read,
              'total_vote' => $c->total_vote,
              'parts' => $c->parts,
          ];
      })
      ->values()
      ->toArray();
  $jsNonRekomen = $ceritaNonRekomen
      ->map(function ($c) {
          return [
              'id' => $c->id,
              'judul' => $c->judul,
              'total_read' => $c->total_read,
              'total_vote' => $c->total_vote,
              'parts' => $c->parts,
          ];
      })
      ->values()
      ->toArray();
@endphp
@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const isDark = document.documentElement.classList.contains('dark-style');
      const textColor = isDark ? '#a1aab2' : '#697a8d';
      const gridColor = isDark ? '#434968' : '#eceef1';
      const topReadCeritas = @json($jsTopRead);
      const topVoteCeritas = @json($jsTopVote);
      const kategoriGroups = @json($jsKategori);
      const statusGroups = [{
          label: 'Aktif',
          ceritas: @json($jsAktif)
        },
        {
          label: 'Nonaktif',
          ceritas: @json($jsNonaktif)
        },
      ];
      const rekomenGroups = [{
          label: 'Rekomendasi',
          ceritas: @json($jsRekomen)
        },
        {
          label: 'Tidak',
          ceritas: @json($jsNonRekomen)
        },
      ];

      function showModal(title, ceritas) {
        document.getElementById('detailModalTitle').textContent = title;
        const tbody = document.getElementById('detailModalBody');
        if (!ceritas || ceritas.length === 0) {
          tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada data.</td></tr>';
        } else {
          tbody.innerHTML = ceritas.map((c, i) => `
                <tr>
                    <td>${i + 1}</td>
                    <td><span class="fw-medium">${c.judul}</span></td>
                    <td>${c.parts}</td>
                    <td>${Number(c.total_read).toLocaleString()}</td>
                    <td>${Number(c.total_vote).toLocaleString()}</td>
                    <td><a href="/cerita/${c.id}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                </tr>`).join('');
        }
        new bootstrap.Modal(document.getElementById('detailModal')).show();
      }

      function shortLabel(str) {
        return str.length > 18 ? str.slice(0, 18) + '…' : str;
      }
      new ApexCharts(document.getElementById('chartTopRead'), {
        chart: {
          type: 'bar',
          height: 290,
          toolbar: {
            show: false
          },
          events: {
            dataPointSelection(e, ctx, cfg) {
              const c = topReadCeritas[cfg.dataPointIndex];
              showModal(`Detail — ${c.judul}`, [c]);
            }
          }
        },
        series: [{
          name: 'Dibaca',
          data: topReadCeritas.map(c => c.total_read)
        }],
        xaxis: {
          categories: topReadCeritas.map(c => shortLabel(c.judul)),
          labels: {
            style: {
              colors: textColor
            },
            rotate: -35
          }
        },
        yaxis: {
          labels: {
            style: {
              colors: textColor
            }
          }
        },
        colors: ['#696cff'],
        plotOptions: {
          bar: {
            borderRadius: 4,
            columnWidth: '60%',
            cursor: 'pointer'
          }
        },
        dataLabels: {
          enabled: false
        },
        grid: {
          borderColor: gridColor
        },
        tooltip: {
          theme: isDark ? 'dark' : 'light'
        },
      }).render();
      new ApexCharts(document.getElementById('chartTopVote'), {
        chart: {
          type: 'bar',
          height: 290,
          toolbar: {
            show: false
          },
          events: {
            dataPointSelection(e, ctx, cfg) {
              const c = topVoteCeritas[cfg.dataPointIndex];
              showModal(`Detail — ${c.judul}`, [c]);
            }
          }
        },
        series: [{
          name: 'Vote',
          data: topVoteCeritas.map(c => c.total_vote)
        }],
        xaxis: {
          categories: topVoteCeritas.map(c => shortLabel(c.judul)),
          labels: {
            style: {
              colors: textColor
            },
            rotate: -35
          }
        },
        yaxis: {
          labels: {
            style: {
              colors: textColor
            }
          }
        },
        colors: ['#03c3ec'],
        plotOptions: {
          bar: {
            borderRadius: 4,
            columnWidth: '60%',
            cursor: 'pointer'
          }
        },
        dataLabels: {
          enabled: false
        },
        grid: {
          borderColor: gridColor
        },
        tooltip: {
          theme: isDark ? 'dark' : 'light'
        },
      }).render();
      new ApexCharts(document.getElementById('chartKategori'), {
        chart: {
          type: 'donut',
          height: 280,
          events: {
            dataPointSelection(e, ctx, cfg) {
              const g = kategoriGroups[cfg.dataPointIndex];
              showModal(`Kategori: ${g.label}`, g.ceritas);
            }
          }
        },
        series: kategoriGroups.map(g => g.ceritas.length),
        labels: kategoriGroups.map(g => g.label),
        colors: ['#696cff', '#03c3ec', '#71dd37', '#ffab00', '#ff3e1d', '#8592a3'],
        legend: {
          position: 'bottom',
          labels: {
            colors: textColor
          }
        },
        dataLabels: {
          enabled: true
        },
        tooltip: {
          theme: isDark ? 'dark' : 'light'
        },
        plotOptions: {
          pie: {
            donut: {
              size: '65%',
              cursor: 'pointer'
            }
          }
        },
      }).render();
      new ApexCharts(document.getElementById('chartStatus'), {
        chart: {
          type: 'pie',
          height: 280,
          events: {
            dataPointSelection(e, ctx, cfg) {
              const g = statusGroups[cfg.dataPointIndex];
              showModal(`Status: ${g.label}`, g.ceritas);
            }
          }
        },
        series: statusGroups.map(g => g.ceritas.length),
        labels: statusGroups.map(g => g.label),
        colors: ['#71dd37', '#8592a3'],
        legend: {
          position: 'bottom',
          labels: {
            colors: textColor
          }
        },
        tooltip: {
          theme: isDark ? 'dark' : 'light'
        },
      }).render();
      new ApexCharts(document.getElementById('chartRekomen'), {
        chart: {
          type: 'pie',
          height: 280,
          events: {
            dataPointSelection(e, ctx, cfg) {
              const g = rekomenGroups[cfg.dataPointIndex];
              showModal(`Rekomendasi: ${g.label}`, g.ceritas);
            }
          }
        },
        series: rekomenGroups.map(g => g.ceritas.length),
        labels: rekomenGroups.map(g => g.label),
        colors: ['#ffab00', '#8592a3'],
        legend: {
          position: 'bottom',
          labels: {
            colors: textColor
          }
        },
        tooltip: {
          theme: isDark ? 'dark' : 'light'
        },
      }).render();
    });
  </script>
@endsection
