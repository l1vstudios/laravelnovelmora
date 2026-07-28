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

    <!-- Include Scripts for customizer, helper, analytics, config -->
    @include('layouts/sections/scriptsIncludes')
</head>

<body>
    <!-- Layout Content -->
    @yield('layoutContent')
    <!--/ Layout Content -->

    

    <!-- Include Scripts -->
    @include('layouts/sections/scripts')
</body>

</html>
