@extends('layouts/contentNavbarLayout')
@section('title', 'Pengguna BACAAN')

@php
    $labels = [
        'id' => 'ID',
        'name' => 'Nama',
        'email' => 'Email',
        'auth_provider' => 'Provider',
        'points' => 'Points',
        'koin' => 'Koin',
        'email_verified_at' => 'Verifikasi Email',
        'last_login_at' => 'Login Terakhir',
        'created_at' => 'Bergabung',
    ];

    $dateColumns = ['email_verified_at', 'last_login_at', 'created_at'];
@endphp

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-6">
            <div>
                <h4 class="mb-1">Pengguna BACAAN</h4>
                <small class="text-muted">Data Pengguna Bacaan</small>
            </div>
            <a href="{{ route('dashboard-analytics') }}" class="btn btn-outline-secondary btn-sm">
                <i class="icon-base bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>

        <div class="card">
            <div class="card-header">
                <form method="GET" action="{{ route('dashboard.mobile-users') }}" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Cari</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nama atau email">
                    </div>
                    <div class="col-md-7 d-flex gap-2 justify-content-md-end">
                        <button class="btn btn-primary">
                            <i class="icon-base bx bx-search me-1"></i> Cari
                        </button>
                        @if(request()->filled('search'))
                            <a href="{{ route('dashboard.mobile-users') }}" class="btn btn-outline-secondary">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th data-sort-column="id">#</th>
                            @foreach($columns as $column)
                                <th data-sort-column="{{ $column }}">{{ $labels[$column] ?? Str::headline($column) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                                @foreach($columns as $column)
                                    @php($value = data_get($user, $column))
                                    <td>
                                        @if($column === 'name')
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar avatar-sm">
                                                    <span class="avatar-initial rounded-circle bg-label-success">{{ strtoupper(substr((string) $value, 0, 1)) ?: '?' }}</span>
                                                </div>
                                                <span class="fw-medium">{{ $value ?: '-' }}</span>
                                            </div>
                                        @elseif($column === 'auth_provider')
                                            <span class="badge bg-label-info">{{ $value ?: '-' }}</span>
                                        @elseif(in_array($column, ['points', 'koin'], true))
                                            <span class="badge bg-label-warning">{{ number_format((int) $value) }}</span>
                                        @elseif(in_array($column, $dateColumns, true))
                                            {{ $value ? \Illuminate\Support\Carbon::parse($value)->format('d M Y, H:i') : '-' }}
                                        @else
                                            {{ $value ?: '-' }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $columns->count() + 1 }}" class="text-center py-6 text-muted">Belum ada data pengguna.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
                <div class="card-footer d-flex justify-content-end">{{ $users->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
