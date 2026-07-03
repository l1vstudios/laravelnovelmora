<!-- BEGIN: Vendor JS-->

@vite(['resources/assets/vendor/libs/jquery/jquery.js', 'resources/assets/vendor/libs/popper/popper.js', 'resources/assets/vendor/js/bootstrap.js'])

@vite(['resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js', 'resources/assets/vendor/js/menu.js'])

@yield('vendor-script')
<!-- END: Page Vendor JS-->
<!-- BEGIN: Theme JS-->
@vite(['resources/assets/js/main.js'])

<!-- END: Theme JS-->
<!-- Pricing Modal JS-->
@stack('pricing-script')
<!-- END: Pricing Modal JS-->
<!-- BEGIN: Page JS-->
@yield('page-script')
<!-- END: Page JS-->

<script>
document.addEventListener('DOMContentLoaded', function () {
    const numberTooLargeMessage = 'Maaf, angka yang dimasukkan terlalu besar.';
    let alertShownAt = 0;

    function showOnce(message) {
        const now = Date.now();
        if (now - alertShownAt < 700) return;
        alertShownAt = now;
        window.alert(message);
    }

    function numberExceedsMax(input) {
        if (input.type !== 'number' || input.max === '' || input.value === '') return false;
        return Number(input.value) > Number(input.max);
    }

    document.addEventListener('invalid', function (event) {
        if (!numberExceedsMax(event.target)) return;
        event.preventDefault();
        showOnce(numberTooLargeMessage);
        event.target.focus();
    }, true);

    document.addEventListener('submit', function (event) {
        const input = Array.from(event.target.querySelectorAll('input[type="number"][max]'))
            .find(numberExceedsMax);
        if (!input || !numberExceedsMax(input)) return;
        event.preventDefault();
        showOnce(numberTooLargeMessage);
        input.focus();
    }, true);

    @php($firstError = $errors->first())
    @if (session('error'))
        showOnce(@json(session('error')));
    @elseif ($firstError === 'Maaf, angka yang dimasukkan terlalu besar.')
        showOnce(numberTooLargeMessage);
    @endif
});
</script>

<!-- app JS -->
@vite(['resources/js/app.js'])
<!-- END: app JS-->
