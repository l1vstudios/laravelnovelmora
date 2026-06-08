@extends('layouts/contentNavbarLayout')
@section('title', 'Hak Akses — ' . $role->name)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-6">
    <div>
        <h4 class="mb-1">Hak Akses Menu</h4>
        <p class="text-muted mb-0">
            Role: <strong>{{ $role->name }}</strong>
            @if($role->is_super_admin)
                <span class="badge bg-label-danger ms-2">Super Admin — akses penuh otomatis</span>
            @endif
        </p>
    </div>
    <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="icon-base bx bx-arrow-back me-1"></i> Kembali
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible mb-6" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($role->is_super_admin)
<div class="alert alert-info mb-6">
    <i class="icon-base bx bx-info-circle me-2"></i>
    Role Super Admin memiliki akses penuh ke semua menu secara otomatis. Pengaturan di bawah ini tidak berpengaruh.
</div>
@endif

<form action="{{ route('roles.update-permissions', $role) }}" method="POST">
    @csrf

    @foreach($menus as $groupLabel => $groupMenus)
    <div class="card mb-4">
        <div class="card-header py-3">
            <h6 class="mb-0 text-uppercase" style="font-size:.75rem;letter-spacing:.08em;">
                {{ $groupLabel ?: 'Umum' }}
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:35%">Menu</th>
                        <th class="text-center">
                            <span class="d-block">Lihat</span>
                            <small class="text-muted fw-normal">View</small>
                        </th>
                        <th class="text-center">
                            <span class="d-block">Tambah</span>
                            <small class="text-muted fw-normal">Insert</small>
                        </th>
                        <th class="text-center">
                            <span class="d-block">Ubah</span>
                            <small class="text-muted fw-normal">Update</small>
                        </th>
                        <th class="text-center">
                            <span class="d-block">Hapus</span>
                            <small class="text-muted fw-normal">Delete</small>
                        </th>
                        <th class="text-center">Pilih Semua</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupMenus as $menu)
                    @php $p = $perms[$menu->id] ?? null; @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <i class="icon-base {{ $menu->icon }} text-primary"></i>
                                <span class="fw-medium">{{ $menu->name }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input perm-check"
                                data-row="row-{{ $menu->id }}"
                                name="permissions[{{ $menu->id }}][can_view]"
                                {{ ($p && $p->can_view) ? 'checked' : '' }}>
                        </td>
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input perm-check"
                                data-row="row-{{ $menu->id }}"
                                name="permissions[{{ $menu->id }}][can_insert]"
                                {{ ($p && $p->can_insert) ? 'checked' : '' }}>
                        </td>
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input perm-check"
                                data-row="row-{{ $menu->id }}"
                                name="permissions[{{ $menu->id }}][can_update]"
                                {{ ($p && $p->can_update) ? 'checked' : '' }}>
                        </td>
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input perm-check"
                                data-row="row-{{ $menu->id }}"
                                name="permissions[{{ $menu->id }}][can_delete]"
                                {{ ($p && $p->can_delete) ? 'checked' : '' }}>
                        </td>
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input select-row"
                                data-row="row-{{ $menu->id }}"
                                title="Pilih semua untuk {{ $menu->name }}">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    <div class="d-flex gap-3 mt-2">
        <button type="submit" class="btn btn-primary">
            <i class="icon-base bx bx-save me-1"></i> Simpan Hak Akses
        </button>
        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Select-all per row toggle
    document.querySelectorAll('.select-row').forEach(function (selectAll) {
        const rowId = selectAll.dataset.row;
        const rowChecks = document.querySelectorAll(`.perm-check[data-row="${rowId}"]`);

        // Set initial state
        selectAll.checked = [...rowChecks].every(c => c.checked);
        selectAll.indeterminate = !selectAll.checked && [...rowChecks].some(c => c.checked);

        selectAll.addEventListener('change', function () {
            rowChecks.forEach(c => c.checked = this.checked);
        });

        rowChecks.forEach(function (check) {
            check.addEventListener('change', function () {
                const allChecked = [...rowChecks].every(c => c.checked);
                const anyChecked = [...rowChecks].some(c => c.checked);
                selectAll.checked = allChecked;
                selectAll.indeterminate = !allChecked && anyChecked;
            });
        });
    });
});
</script>
@endsection
@endsection
