@extends('layouts/contentNavbarLayout')
@section('title', 'Earning')

@section('content')
<div class="row">
    {{-- Summary Cards --}}
    <div class="col-lg-3 col-md-6 col-sm-6 mb-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-3">
                        <span class="avatar-initial rounded bg-label-primary"><i class="icon-base bx bx-trending-up"></i></span>
                    </div>
                    <div>
                        <p class="mb-0 text-muted" style="font-size:.75rem;">Total Gross</p>
                        <h5 class="mb-0">Rp {{ number_format($summary['total_gross'], 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 mb-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-3">
                        <span class="avatar-initial rounded bg-label-success"><i class="icon-base bx bx-check-circle"></i></span>
                    </div>
                    <div>
                        <p class="mb-0 text-muted" style="font-size:.75rem;">Total Bersih (setelah 30% Google)</p>
                        <h5 class="mb-0">Rp {{ number_format($summary['total_net'], 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 mb-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-3">
                        <span class="avatar-initial rounded bg-label-info"><i class="icon-base bx bx-package"></i></span>
                    </div>
                    <div>
                        <p class="mb-0 text-muted" style="font-size:.75rem;">Transaksi Paket</p>
                        <h5 class="mb-0">{{ number_format($summary['count_paket'], 0, ',', '.') }}</h5>
                        <small class="text-muted">Rp {{ number_format($summary['total_paket'], 0, ',', '.') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 mb-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-3">
                        <span class="avatar-initial rounded bg-label-warning"><i class="icon-base bx bx-coin"></i></span>
                    </div>
                    <div>
                        <p class="mb-0 text-muted" style="font-size:.75rem;">Transaksi Koin</p>
                        <h5 class="mb-0">{{ number_format($summary['count_koin'], 0, ',', '.') }}</h5>
                        <small class="text-muted">Rp {{ number_format($summary['total_koin'], 0, ',', '.') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Riwayat Earning</h5>
            </div>
            <div class="card-body border-bottom pb-4 pt-3">
                <form method="GET" action="{{ route('earning.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label mb-1 text-muted" style="font-size:.75rem;">Tipe</label>
                        <select name="type" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            <option value="paket" {{ $type === 'paket' ? 'selected' : '' }}>Paket</option>
                            <option value="koin" {{ $type === 'koin' ? 'selected' : '' }}>Koin</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label mb-1 text-muted" style="font-size:.75rem;">Cari (nama/email/trx id)</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari..." value="{{ $search }}">
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label mb-1 text-muted" style="font-size:.75rem;">Dari Tanggal</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label mb-1 text-muted" style="font-size:.75rem;">Sampai Tanggal</label>
                        <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-3 col-sm-12 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="icon-base bx bx-filter me-1"></i> Filter</button>
                        @if(request()->hasAny(['type', 'search', 'start_date', 'end_date']))
                        <a href="{{ route('earning.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Tipe</th>
                            <th>Deskripsi</th>
                            <th>Total (Gross)</th>
                            <th>Total Bersih</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($earnings as $earning)
                        <tr>
                            <td>{{ $loop->iteration + ($earnings->currentPage() - 1) * $earnings->perPage() }}</td>
                            <td>
                                <div>
                                    <span class="fw-medium">{{ $earning->user_name ?? '-' }}</span>
                                    <br><small class="text-muted">{{ $earning->user_email ?? '-' }}</small>
                                </div>
                            </td>
                            <td>
                                @if($earning->type === 'paket')
                                <span class="badge bg-label-info">Paket</span>
                                @else
                                <span class="badge bg-label-warning">Koin</span>
                                @endif
                            </td>
                            <td>{{ $earning->description ?? '-' }}</td>
                            <td>Rp {{ number_format((int) $earning->amount_price, 0, ',', '.') }}</td>
                            <td>
                                <span class="text-success fw-medium">Rp {{ number_format((int) round($earning->amount_price * 0.70), 0, ',', '.') }}</span>
                            </td>
                            <td>
                                @if($earning->start_date)
                                {{ \Carbon\Carbon::parse($earning->start_date)->format('d M Y') }}
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($earning->end_date)
                                {{ \Carbon\Carbon::parse($earning->end_date)->format('d M Y') }}
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($earning->status_payment === 'verified')
                                <span class="badge bg-label-success">Verified</span>
                                @elseif($earning->status_payment === 'pending')
                                <span class="badge bg-label-warning">Pending</span>
                                @else
                                <span class="badge bg-label-secondary">{{ $earning->status_payment ?? '-' }}</span>
                                @endif
                            </td>
                            <td>{{ $earning->created_at ? \Carbon\Carbon::parse($earning->created_at)->format('d M Y H:i') : '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="10" class="text-center py-6 text-muted">Belum ada data transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($earnings->hasPages())
            <div class="card-footer d-flex justify-content-end">{{ $earnings->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
