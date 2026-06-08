@isset($pageConfigs)
{!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@extends('layouts/commonMaster')

@php
/* Display elements */
$contentNavbar = $contentNavbar ?? true;
$containerNav = $containerNav ?? 'container-xxl';
$isNavbar = $isNavbar ?? true;
$isMenu = $isMenu ?? true;
$isFlex = $isFlex ?? false;
$isFooter = $isFooter ?? true;
$customizerHidden = $customizerHidden ?? '';

/* HTML Classes */
$navbarDetached = 'navbar-detached';
$menuFixed = isset($configData['menuFixed']) ? $configData['menuFixed'] : '';
if (isset($navbarType)) {
$configData['navbarType'] = $navbarType;
}
$navbarType = isset($configData['navbarType']) ? $configData['navbarType'] : '';
$footerFixed = isset($configData['footerFixed']) ? $configData['footerFixed'] : '';
$menuCollapsed = isset($configData['menuCollapsed']) ? $configData['menuCollapsed'] : '';

/* Content classes */
$container = ($container ?? 'container-xxl');

@endphp

@section('layoutContent')
<div class="layout-wrapper layout-content-navbar {{ $isMenu ? '' : 'layout-without-menu' }}">
    <div class="layout-container">

        @if ($isMenu)
        @include('layouts/sections/menu/verticalMenu')
        @endif


        <!-- Layout page -->
        <div class="layout-page">

            {{-- Below commented code read by artisan command while installing jetstream. !! Do not remove if you want to use jetstream. --}}
            {{-- <x-banner /> --}}

            <!-- BEGIN: Navbar-->
            @if ($isNavbar)
            @include('layouts/sections/navbar/navbar')
            @endif
            <!-- END: Navbar-->


            <!-- Content wrapper -->
            <div class="content-wrapper">

                <!-- Content -->
                @if ($isFlex)
                <div class="{{ $container }} d-flex align-items-stretch flex-grow-1 p-0">
                    @else
                    <div class="{{ $container }} flex-grow-1 container-p-y">
                        @endif

                        @yield('content')

                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    @if ($isFooter)
                    @include('layouts/sections/footer/footer')
                    @endif
                    <!-- / Footer -->
                    <div class="content-backdrop fade"></div>
                </div>
                <!--/ Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        @if ($isMenu)
        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
        @endif
        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->
</div>

{{-- Global Confirm Modal --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div class="avatar avatar-md mx-auto mt-2">
                    <span class="avatar-initial rounded-circle bg-label-danger">
                        <i class="icon-base bx bx-trash icon-md"></i>
                    </span>
                </div>
            </div>
            <div class="modal-body text-center px-5 py-4">
                <h5 class="mb-2">Konfirmasi Hapus</h5>
                <p class="text-muted mb-0" id="confirmModalMessage">Yakin ingin menghapus data ini?</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-3 pb-5">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmModalBtn">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    let _pendingForm = null;

    document.addEventListener('submit', function (e) {
        const form = e.target;
        const msg  = form.dataset.confirm;
        if (!msg) return;
        e.preventDefault();
        document.getElementById('confirmModalMessage').textContent = msg;
        _pendingForm = form;
        new bootstrap.Modal(document.getElementById('confirmModal')).show();
    });

    document.getElementById('confirmModalBtn').addEventListener('click', function () {
        if (_pendingForm) {
            const f = _pendingForm;
            _pendingForm = null;
            delete f.dataset.confirm;
            f.submit();
        }
    });
})();
</script>

@endsection
