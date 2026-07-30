<!DOCTYPE html>
<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="{{ asset('/assets') . '/' }}" dir="ltr" data-skin="default" data-base-url="{{ url('/') }}" data-framework="laravel" data-bs-theme="light" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>
        @hasSection('pageTitle')
            @yield('pageTitle')
        @else
            @yield('title') | {{ config('variables.templateName') ? config('variables.templateName') : 'TemplateName' }}
            - {{ config('variables.templateSuffix') ? config('variables.templateSuffix') : 'TemplateSuffix' }}
        @endif
    </title>
    @hasSection('basicMetaOnly')
        <meta name="description" content="@yield('meta_description')" />
        <meta property="og:site_name" content="@yield('og_site_name')" />
        <meta name="robots" content="@yield('robots', 'noindex, nofollow')" />
    @else
        <meta name="description" content="@yield('meta_description', config('variables.templateDescription') ? config('variables.templateDescription') : '')" />
        <meta name="keywords" content="@yield('meta_keywords', config('variables.templateKeyword') ? config('variables.templateKeyword') : '')" />
        <meta property="og:title" content="@yield('og_title', config('variables.ogTitle') ? config('variables.ogTitle') : '')" />
        <meta property="og:type" content="{{ config('variables.ogType') ? config('variables.ogType') : '' }}" />
        <meta property="og:url" content="{{ config('variables.productPage') ? config('variables.productPage') : '' }}" />
        <meta property="og:image" content="{{ config('variables.ogImage') ? config('variables.ogImage') : '' }}" />
        <meta property="og:description" content="@yield('meta_description', config('variables.templateDescription') ? config('variables.templateDescription') : '')" />
        <meta property="og:site_name" content="@yield('og_site_name', config('variables.creatorName') ? config('variables.creatorName') : '')" />
        <meta name="robots" content="@yield('robots', 'noindex, nofollow')" />
    @endif
    @sectionMissing('basicMetaOnly')
        <!-- laravel CRUD token -->
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <!-- Canonical SEO -->
        <link rel="canonical" href="{{ config('variables.productPage') ? config('variables.productPage') : '' }}" />
    @endif
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Include Styles -->
    @include('layouts/sections/styles')

    <style>
        table thead th {
            user-select: none;
            white-space: nowrap;
        }

        table thead th.sortable-grid-header {
            cursor: pointer;
            position: relative;
        }

        table thead th.sortable-grid-header .grid-sort-label {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
        }

        table thead th.sortable-grid-header .grid-sort-icons {
            display: inline-flex;
            flex-direction: column;
            justify-content: center;
            line-height: .65;
            font-size: .75rem;
            opacity: .45;
            vertical-align: middle;
        }

        table thead th.sortable-grid-header .grid-sort-icons i {
            height: .55rem;
            line-height: .55rem;
        }

        table thead th.sortable-grid-header.sort-asc .grid-sort-icons .grid-sort-up,
        table thead th.sortable-grid-header.sort-desc .grid-sort-icons .grid-sort-down {
            opacity: 1;
            color: var(--bs-primary);
            font-weight: 700;
        }

        table thead th.sortable-grid-header.sort-asc .grid-sort-icons .grid-sort-down,
        table thead th.sortable-grid-header.sort-desc .grid-sort-icons .grid-sort-up {
            opacity: .25;
        }
    </style>

    <!-- Include Scripts for customizer, helper, analytics, config -->
    @include('layouts/sections/scriptsIncludes')
</head>

<body>
    <!-- Layout Content -->
    @yield('layoutContent')
    <!--/ Layout Content -->



    <!-- Include Scripts -->
    @include('layouts/sections/scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('table.table-hover').forEach(function (table) {
                if (table.getAttribute('data-grid-sortable') === 'false') return;

                const thead = table.tHead;
                const tbody = table.tBodies[0];

                if (!thead || !tbody) return;

                const headerRow = thead.rows[thead.rows.length - 1];
                if (!headerRow) return;

                Array.from(headerRow.cells).forEach(function (th, columnIndex) {
                    if (th.hasAttribute('data-sortable') && th.getAttribute('data-sortable') === 'false') return;

                    const headerText = th.innerText.replace(/[▲▼↑↓]/g, '').replace(/\s+/g, ' ').trim();
                    const normalizedHeader = headerText.toLowerCase().replace(/\s+/g, '_');
                    const ignoredHeaders = ['', 'aksi', 'preview', 'cover', 'video_hari_ini', 'pilih_semua'];

                    if (ignoredHeaders.includes(normalizedHeader)) return;

                    th.classList.add('sortable-grid-header');

                    if (!th.querySelector('.grid-sort-icons')) {
                        const currentContent = th.innerHTML.trim();
                        th.innerHTML = '<span class="grid-sort-label"><span class="grid-sort-text">' + currentContent + '</span><span class="grid-sort-icons" aria-hidden="true"><i class="grid-sort-up">▲</i><i class="grid-sort-down">▼</i></span></span>';
                    }

                    const urlParams = new URLSearchParams(window.location.search);
                    const currentSort = urlParams.get('sort') || urlParams.get('sort_by');
                    const currentDirection = urlParams.get('direction') || urlParams.get('dir') || urlParams.get('sort_dir') || 'desc';
                    const sortColumn = th.getAttribute('data-sort-column') || th.getAttribute('data-column') || (normalizedHeader === '#' ? 'id' : normalizedHeader);

                    if (currentSort === sortColumn) {
                        th.classList.add(currentDirection === 'asc' ? 'sort-asc' : 'sort-desc');
                        th.setAttribute('aria-sort', currentDirection === 'asc' ? 'ascending' : 'descending');
                    } else {
                        th.setAttribute('aria-sort', 'none');
                    }

                    th.addEventListener('click', function (event) {
                        event.preventDefault();

                        const params = new URLSearchParams(window.location.search);
                        const activeSort = params.get('sort') || params.get('sort_by');
                        const activeDirection = params.get('direction') || params.get('dir') || params.get('sort_dir') || 'desc';
                        const direction = activeSort === sortColumn && activeDirection === 'asc' ? 'desc' : 'asc';

                        params.set('sort', sortColumn);
                        params.set('direction', direction);
                        params.delete('dir');
                        params.delete('sort_by');
                        params.delete('sort_dir');
                        params.delete('page');

                        window.location.href = window.location.pathname + '?' + params.toString();
                    });
                });
            });
        });
    </script>
</body>

</html>
