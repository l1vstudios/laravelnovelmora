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
            cursor: pointer;
            user-select: none;
            white-space: nowrap;
        }

        table thead th.sortable-grid-header {
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
            const collator = new Intl.Collator('id-ID', { numeric: true, sensitivity: 'base' });

            function cellValue(row, index) {
                const cell = row.children[index];
                if (!cell) return '';

                const explicitValue = cell.getAttribute('data-sort') || cell.getAttribute('data-order');
                if (explicitValue !== null) return explicitValue.trim();

                return (cell.innerText || cell.textContent || '').replace(/\s+/g, ' ').trim();
            }

            function comparableValue(value) {
                const normalized = value.replace(/\./g, '').replace(',', '.');
                const numeric = Number(normalized);

                if (value !== '' && !Number.isNaN(numeric) && /^-?[\d.,]+$/.test(value)) {
                    return { type: 'number', value: numeric };
                }

                const parsedDate = Date.parse(value);
                if (value !== '' && !Number.isNaN(parsedDate) && /(\d{1,4}[-/ ]|[A-Za-z]{3,})/.test(value)) {
                    return { type: 'date', value: parsedDate };
                }

                return { type: 'text', value: value.toLowerCase() };
            }

            function compareValues(a, b) {
                const left = comparableValue(a);
                const right = comparableValue(b);

                if (left.type === right.type && left.type !== 'text') {
                    return left.value === right.value ? 0 : (left.value > right.value ? 1 : -1);
                }

                return collator.compare(String(left.value), String(right.value));
            }

            function sortableRows(tbody) {
                return Array.from(tbody.querySelectorAll(':scope > tr')).filter(function (row) {
                    return row.children.length > 1 && !row.querySelector('td[colspan]');
                });
            }

            document.querySelectorAll('table').forEach(function (table) {
                const thead = table.tHead;
                const tbody = table.tBodies[0];

                if (!thead || !tbody) return;

                const headerRow = thead.rows[thead.rows.length - 1];
                if (!headerRow) return;

                Array.from(headerRow.cells).forEach(function (th, columnIndex) {
                    if (th.hasAttribute('data-sortable') && th.getAttribute('data-sortable') === 'false') return;

                    th.classList.add('sortable-grid-header');

                    if (!th.querySelector('.grid-sort-icons')) {
                        const currentContent = th.innerHTML.trim();
                        th.innerHTML = '<span class="grid-sort-label"><span class="grid-sort-text">' + currentContent + '</span><span class="grid-sort-icons" aria-hidden="true"><i class="grid-sort-up">▲</i><i class="grid-sort-down">▼</i></span></span>';
                    }

                    th.addEventListener('click', function (event) {
                        event.preventDefault();

                        const direction = th.classList.contains('sort-asc') ? 'desc' : 'asc';
                        const rows = sortableRows(tbody);

                        rows.sort(function (rowA, rowB) {
                            const compared = compareValues(cellValue(rowA, columnIndex), cellValue(rowB, columnIndex));
                            return direction === 'asc' ? compared : compared * -1;
                        });

                        Array.from(headerRow.cells).forEach(function (header) {
                            header.classList.remove('sort-asc', 'sort-desc');
                            header.setAttribute('aria-sort', 'none');
                        });

                        th.classList.add(direction === 'asc' ? 'sort-asc' : 'sort-desc');
                        th.setAttribute('aria-sort', direction === 'asc' ? 'ascending' : 'descending');

                        rows.forEach(function (row) {
                            tbody.appendChild(row);
                        });
                    });
                });
            });
        });
    </script>
</body>

</html>
