<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('includes.style')
    @stack('addon-style')
</head>

<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled">
    <div class="d-flex flex-column flex-root">
        <div class="flex-row page d-flex flex-column-fluid">
            <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
                @include('partials.navbar')
                <div id="kt_toolbar_container" class="flex-wrap mt-5 container-xxl d-flex flex-stack">
                    <!--begin::Page title-->
                    <div class="page-title d-flex flex-column me-3">
                        <!--begin::Title-->
                        <h1 class="my-1 d-flex text-dark fw-bold fs-3">@yield('menu-title')</h1>
                        <!--end::Title-->
                        <!--begin::Breadcrumb-->
                        <ul class="my-1 text-gray-600 breadcrumb breadcrumb-dot fw-semibold fs-7">
                            <!--begin::Item-->
                            <li class="text-gray-600 breadcrumb-item">
                                <a href="/dashboard" class="text-gray-600 text-hover-primary">Dashboard</a>
                            </li>
                            <!--end::Item-->
                            <!--begin::Item-->
                            <li class="text-gray-600 breadcrumb-item">@yield('menu-title')</li>
                            <!--end::Item-->
                        </ul>
                        <!--end::Breadcrumb-->
                    </div>
                    <!--end::Page title-->
                </div>
                <div id="kt_content_container" class="d-flex flex-column-fluid align-items-start container-xxl">
                    <div class="content flex-row-fluid" id="kt_content">
                        <div class="row gy-0 gx-10">
                            <x-alert />
                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('includes.script')
    @stack('addon-script')
</body>

</html>