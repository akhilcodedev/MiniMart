<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="{{ config('app.name') }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <title>@yield('page-title') - {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }} ">

    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="admin" />
    <meta property="og:title" content="@yield('page-title') - {{ config('app.name') }}" />
    <meta property="og:url" content="{{ url('/') }}" />
    <meta property="og:site_name" content="{{ config('app.name') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="canonical" href="{{ url('/') }}" />
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700">
    <!--end::Fonts-->
    <!--begin::Page Custom Styles(used by this page)-->
    <link href="{{ asset('build-base/ktmt/plugins/custom/fullcalendar/fullcalendar.bundle.css') }}" rel="stylesheet"
        type="text/css" />

    <link href="{{ asset('build-base/ktmt/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
        type="text/css" />

    <!--end::Page Custom Styles-->
    <!--begin::Global Theme Styles(used by all pages)-->
    <link href="{{ asset('build-base/ktmt/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('build-base/ktmt/plugins/custom/prismjs/prismjs.bundle.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('build-base/ktmt/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{asset('build-base/assets/css/new-dashboard.css')}}" rel="stylesheet" type="text/css" />

    {{-- Include Metronic styles --}}
    <link href="{{ asset('metronic/assets/plugins/custom/fullcalendar/fullcalendar.bundle.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('metronic/assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('metronic/assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    {{-- Include Animate.css for additional animations --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <!--end::Global Theme Styles-->
    <!--begin::Layout Themes(used by all pages)-->
    <!--end::Layout Themes-->
    <script src="{{ asset('metronic/assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('metronic/assets/js/scripts.bundle.js') }}"></script>
    <script src="{{ asset('metronic/assets/plugins/custom/fullcalendar/fullcalendar.bundle.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    {{-- Laravel Vite - CSS File --}}
    {{-- {{ module_vite('build-base', 'Resources/assets/sass/app.scss') }} --}}

    {{ module_vite('build-base', 'resources/assets/sass/app.scss') }}
    {{ module_vite('build-base', 'resources/assets/sass/responsive.scss') }}

    @yield('custom-css-section')

    <link rel="icon" type="image/x-icon" href="{{asset('assets/image/dispatchers.png ')}}" />

    @yield('initialize-js-section')
    <style>
        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .table tbody tr {
            transition: background-color 0.3s ease;
        }

        .table tbody tr:hover {
            background-color: #f1f3fa;
        }
    </style>
</head>

<body id="kt_body"
    class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed toolbar-tablet-and-mobile-fixed aside-enabled aside-fixed"
    style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">

    {{ module_vite('build-base', 'resources/assets/js/themeColor.js') }}
    <!--begin::Main-->

    <!--begin::Root-->
    <div class="d-flex flex-column flex-root">
        <!--begin::Page-->
        <div class="page d-flex flex-row flex-column-fluid page" id="main_page_wrapper_area">

            <!--begin::Aside-->
            @include('base::layouts.partials.mt-sidebar')
            <!--end::Aside-->

            <!--begin::Wrapper-->
            <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">

                <!--begin::Header-->
                @include('base::layouts.partials.mt-header')
                <!--end::Header-->

                <!--begin::Content-->
                <div class="content d-flex flex-column flex-column-fluid" id="kt_content">

                    <!--begin::Subheader Toolbar -->
                    <div class="toolbar" id="kt_toolbar">

                        <!--begin::Container-->
                        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">

                            <!--begin::Page title-->
                            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                                class="page-title d-flex align-items-center me-3 flex-wrap lh-1">

                                <!--begin::Title-->
                                <h1 class="d-flex align-items-center text-gray-900 fw-bold my-1 fs-3">
                                    @yield('page-sub-title')</h1>
                                <!--end::Title-->

                                <!--begin::Separator-->
                                <span class="h-20px border-gray-200 border-start mx-4"></span>
                                <!--end::Separator-->

                                <!--begin::Breadcrumb-->
                                @include('base::layouts.partials.mt-breadcrumbs')
                                <!--end::Breadcrumb-->

                            </div>
                            <!--end::Page title-->

                        </div>
                        <!--end::Container-->

                    </div>
                    <!--end::Subheader Toolbar -->

                    <!--begin::Entry-->
                    <div class="post d-flex flex-column-fluid" id="kt_post">


                        <div id="kt_content_container" class="container-fluid">
                            @if(session()->has('success'))
                                <div class="alert alert-custom alert-success alert-light-success d-flex flex-column flex-sm-row w-100 p-5 mb-10 fade show"
                                    role="alert">
                                    <!-- <div class="alert-icon"><i class="flaticon2-check-mark"></i></div>
                                    <div class="alert-text">{{ session()->get('success') }}</div>
                                    <div class="alert-close">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true"><i class="ki ki-close"></i></span>
                                        </button>
                                    </div> -->
                                    <i class="ki-duotone ki-notification-bing fs-2hx text-success me-4 mb-5 mb-sm-0">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    <div class="d-flex flex-column justify-content-center text-light pe-0 pe-sm-10">
                                        <h4 class="mb-2 light">{{ session()->get('success') }}</h4>
                                    </div>
                                    <button type="button"
                                        class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                                        data-bs-dismiss="alert">
                                        <i class="ki-duotone ki-cross fs-1 text-success"><span class="path1"></span><span
                                                class="path2"></span></i>
                                    </button>
                                </div>
                            @endif

                            @if(session()->has('message'))
                                <div class="alert alert-custom alert-dark alert-light-dark d-flex flex-column flex-sm-row w-100 p-5 mb-10 fade show"
                                    role="alert">
                                    <script src="https://code.jquery.com/jquery-1.12.3.min.js"></script>
                                    <script src="//cdn.ckeditor.com/4.5.9/standard/ckeditor.js"></script>
                                    <script
                                        src="//cdnjs.cloudflare.com/ajax/libs/ckeditor/4.5.9/adapters/jquery.js"></script>
                                    s="alert alert-custom alert-info alert-light-info d-flex flex-column flex-sm-row w-100
                                    p-5 mb-10 fade show" role="alert">
                                    <!-- <div class="alert-icon"><i class="flaticon-information"></i></div>
                                    <div class="alert-text">{{ session()->get('message') }}</div>
                                    <div class="alert-close">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true"><i class="ki ki-close"></i></span>
                                        </button>
                                    </div> -->
                                    <i class="ki-duotone ki-notification-bing fs-2hx text-info me-4 mb-5 mb-sm-0">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    <div class="d-flex flex-column justify-content-center text-light pe-0 pe-sm-10">
                                        <h4 class="mb-2 light">{{ session()->get('message') }}</h4>
                                    </div>
                                    <button type="button"
                                        class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                                        data-bs-dismiss="alert">
                                        <i class="ki-duotone ki-cross fs-1 text-info"><span class="path1"></span><span
                                                class="path2"></span></i>
                                    </button>
                                </div>
                            @endif

                            @if(session()->has('error'))
                                <div class="alert alert-custom alert-danger alert-light-danger d-flex flex-column flex-sm-row w-100 p-5 mb-10 fade show"
                                    role="alert">
                                    <!-- <div class="alert-icon"><i class="flaticon2-warning"></i></div>
                                    <div class="alert-text">{{ session()->get('error') }}</div>
                                    <div class="alert-close">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true"><i class="ki ki-close"></i></span>
                                        </button>
                                    </div> -->
                                    <i class="ki-duotone ki-notification-bing fs-2hx text-danger me-4 mb-5 mb-sm-0">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    <div class="d-flex flex-column justify-content-center text-light pe-0 pe-sm-10">
                                        <h4 class="mb-2 light">{{ session()->get('error') }}</h4>
                                    </div>
                                    <button type="button"
                                        class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                                        data-bs-dismiss="alert">
                                        <i class="ki-duotone ki-cross fs-1 text-danger"><span class="path1"></span><span
                                                class="path2"></span></i>
                                    </button>
                                </div>
                            @endif

                            @if($errors->any())
                                <div class="alert alert-custom alert-danger alert-light-danger d-flex flex-column flex-sm-row w-100 p-5 mb-10 fade show"
                                    role="alert">
                                    <!-- <div class="alert-icon"><i class="flaticon2-warning"></i></div>
                                    <div class="alert-text">
                                        <ul class="list-unstyled">
                                            {!! implode('', $errors->all('<li><span>:message</span></li>')) !!}
                                        </ul>
                                    </div>
                                    <div class="alert-close">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true"><i class="ki ki-close"></i></span>
                                        </button>
                                    </div> -->
                                    <i class="ki-duotone ki-notification-bing fs-2hx text-danger me-4 mb-5 mb-sm-0">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    <div class="d-flex flex-column justify-content-center text-light pe-0 pe-sm-10">
                                        <h4 class="mb-2 light">{!! implode('', $errors->all('<span>:message</span>')) !!}
                                        </h4>
                                    </div>
                                    <button type="button"
                                        class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                                        data-bs-dismiss="alert">
                                        <i class="ki-duotone ki-cross fs-1 text-danger"><span class="path1"></span><span
                                                class="path2"></span></i>
                                    </button>
                                </div>
                            @endif

                            <div class="custom_alert_trigger_messages_area">

                            </div>

                            @yield('content')

                        </div>

                    </div>
                    <!--end::Entry-->

                </div>
                <!--end::Content-->

                @yield('content-before-footer')

                <!--begin::Footer-->
                @include('base::layouts.partials.mt-footer')
                <!--end::Footer-->

            </div>
            <!--end::Wrapper-->


        </div>
        <!--end::Page-->
    </div>
    <!--end::Root-->

    <!--end::Main-->
    <!--begin::Scrolltop-->
    <div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
        <i class="ki-outline ki-arrow-up"></i>
    </div>
    <!--end::Scrolltop-->

    <!--begin::Javascript-->
    <script>
        var hostUrl = "{{ url('/') }}";
    </script>
    <script>
        var KTAppSettings = {
            "breakpoints": {
                "sm": 576,
                "md": 768,
                "lg": 992,
                "xl": 1200,
                "xxl": 1200
            },
            "colors": {
                "theme": {
                    "base": {
                        "white": "#ffffff",
                        "primary": "#8950FC",
                        "secondary": "#E5EAEE",
                        "success": "#1BC5BD",
                        "info": "#8950FC",
                        "warning": "#FFA800",
                        "danger": "#F64E60",
                        "light": "#F3F6F9",
                        "dark": "#212121"
                    },
                    "light": {
                        "white": "#ffffff",
                        "primary": "#E1E9FF",
                        "secondary": "#ECF0F3",
                        "success": "#C9F7F5",
                        "info": "#EEE5FF",
                        "warning": "#FFF4DE",
                        "danger": "#FFE2E5",
                        "light": "#F3F6F9",
                        "dark": "#D6D6E0"
                    },
                    "inverse": {
                        "white": "#ffffff",
                        "primary": "#ffffff",
                        "secondary": "#212121",
                        "success": "#ffffff",
                        "info": "#ffffff",
                        "warning": "#ffffff",
                        "danger": "#ffffff",
                        "light": "#464E5F",
                        "dark": "#ffffff"
                    }
                },
                "gray": {
                    "gray-100": "#F3F6F9",
                    "gray-200": "#ECF0F3",
                    "gray-300": "#E5EAEE",
                    "gray-400": "#D6D6E0",
                    "gray-500": "#B5B5C3",
                    "gray-600": "#80808F",
                    "gray-700": "#464E5F",
                    "gray-800": "#1B283F",
                    "gray-900": "#212121"
                }
            },
            "font-family": "Poppins"
        };
    </script>
    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
    <script src="{{ asset('build-base/ktmt/plugins/global/plugins.bundle.js') }}"></script>
    <!--end::Global Javascript Bundle-->
    <!--begin::Vendors Javascript(used for this page only)-->
    <script src="{{ asset('build-base/ktmt/plugins/custom/fullcalendar/fullcalendar.bundle.js') }}"></script>
    <script src="{{ asset('build-base/ktmt/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <!--end::Vendors Javascript-->
    <!--begin::Custom Javascript(used for this page only)-->
    {{--
    <script src="{{ asset('build-base/ktmt/js/.bundle.js') }}"></script>--}}
    <script src="{{ asset('build-base/ktmt/js/scripts.bundle.js') }}"></script>

    {{-- Laravel Vite - JS File --}}
    {{ module_vite('build-base', 'resources/assets/js/app.js') }}
    @yield('custom-js-section')

    <!--end::Custom Javascript-->
    <!--end::Javascript-->

</body>
<script>
    // $(document).ready(function () {
    //     function highlightActiveMenuItem() {
    //         var currentUrl = window.location.href;

    //         $(".menu-link").each(function () {
    //             var menuItemUrl = $(this).attr("href");

    //             $(this).removeClass('active');

    //             if (currentUrl === menuItemUrl) {
    //                 $(this).addClass('active');
    //             }
    //         });
    //     }

    //     highlightActiveMenuItem();
    // });

    $(document).ready(function () {
        function highlightActiveMenuItem() {
            var currentUrl = window.location.href;

            $(".menu-link").each(function () {
                var menuItemUrl = $(this).attr("href");

                // Check if the current URL matches the menu item
                if (currentUrl === menuItemUrl) {
                    $(this).addClass('active');

                    // Add 'show' class to open the parent submenu and 'active' class to parent menu
                    $(this).closest(".menu-sub-accordion").addClass('show');
                    $(this).closest(".menu-accordion").addClass('here show');
                } else {
                    $(this).removeClass('active');
                }
            });
        }

        highlightActiveMenuItem();

        // Click function to toggle the accordion
        $(".menu-accordion .menu-link").click(function () {
            var parentAccordion = $(this).closest(".menu-accordion");
            var subMenu = parentAccordion.find(".menu-sub-accordion");

            // Toggle 'show' class for submenu visibility
            subMenu.toggleClass('show');
            // parentAccordion.toggleClass('active');
        });
    });
</script>

</html>