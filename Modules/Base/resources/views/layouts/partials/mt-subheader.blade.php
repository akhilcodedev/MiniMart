<div class="toolbar" id="kt_toolbar">

    <!--begin::Container-->
    <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">

        <!--begin::Page title-->
        <div data-kt-swapper="true" data-kt-swapper-mode="prepend" data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}" class="page-title d-flex align-items-center me-3 flex-wrap lh-1">

            <!--begin::Title-->
            <h1 class="d-flex align-items-center text-gray-900 fw-bold my-1 fs-3">@yield('page-sub-title')</h1>
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

