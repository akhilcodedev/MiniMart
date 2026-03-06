<?php
use Modules\StoreManagement\Models\Store;

$stores = Store::where('status', 1)->get();
$storeId = session('store_id');
?>

<div id="kt_header" style="" class="header align-items-stretch">
    <div class="container-fluid d-flex align-items-stretch justify-content-between">
        <!--begin::Aside mobile toggle-->
        <div class="d-flex align-items-center d-lg-none ms-n4 me-1" title="Show aside menu">
            <div class="btn btn-icon btn-active-color-white" id="kt_aside_mobile_toggle">
                <i class="ki-outline ki-burger-menu fs-1"></i>
            </div>
        </div>
        <!--end::Aside mobile toggle-->
        <!--begin::Mobile logo-->
        <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
            <a href="{{ url('/') }}" class="d-lg-none">
                <img alt="{{ config('app.name') }}" src="{{ asset('build-base/ktmt/media/logos/logo-1.svg') }}" class="h-25px" />
            </a>
        </div>
        <div class="d-flex align-items-stretch justify-content-end gap-20 flex-lg-grow-1">
            <!--begin::Left-->
            <div class="d-flex align-items-stretch mr-2">
                <!--begin::Page Title-->
                <h3 class="d-none text-light d-lg-flex align-items-center mr-10 mb-0">@yield('page-title')</h3>
                <!--end::Page Title-->
            </div>
            <!--end::Left-->
            <div class="d-flex align-items-center">
                <a href="#" class="topbar-item px-3 px-lg-4" data-kt-menu-trigger="{default:'click', lg: 'hover'}"
                    data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                    {{ session('store_id') ? \Modules\StoreManagement\Models\Store::find(session('store_id'))->name : 'Store' }}
                </a>
                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px"
                    data-kt-menu="true" data-kt-element="theme-mode-menu">
                    @foreach ($stores as $store)
                    <div class="menu-item px-3 my-0">
                        <a href="{{ route('store.switch', ['store_id' => $store->id]) }}" class="menu-link px-3 py-2 {{ session('store_id') == $store->id ? 'active' : '' }}">
                            <span class="menu-title">{{ $store->name }}</span>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>

            <!--begin::User-->
            <div class="d-flex align-items-stretch" id="kt_header_user_menu_toggle">
                <!--begin::Menu wrapper-->
                <div class="topbar-item cursor-pointer symbol symbol-circle px-3 px-lg-5 me-n3 me-lg-n5 symbol-30px symbol-md-35px"
                    data-kt-menu-trigger="click" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end" data-kt-menu-flip="bottom">
                    <img src="{{ Auth::user()->image_path ? asset('storage/' . Auth::user()->image_path) : asset('assets/media/avatars/300-1.jpg') }}" alt="{{ config('app.name') }}" />
                </div>
                <!--begin::User account menu-->
                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px" data-kt-menu="true">
                    <!--begin::Menu item-->
                    <div class="menu-item px-3">
                        <div class="menu-content d-flex align-items-center px-3">
                            <!--begin::Avatar-->
                            <div class="symbol symbol-circle symbol-50px me-5">
                                <img src="{{ Auth::user()->image_path ? asset('storage/' . Auth::user()->image_path) : asset('assets/media/avatars/300-1.jpg') }}" />
                            </div>
                            <!--end::Avatar-->
                            <!--begin::Username-->
                            <div class="d-flex flex-column">
                                <a href="#" class="fs-3 text-gray-800 text-hover-primary fw-bold mb-1">{{ Auth::user()->name }}</a>
                                <a href="#" class="fs-5 fw-semibold text-muted text-hover-primary mb-6">{{ Auth::user()->email }}</a>
                            </div>
                            <!--end::Username-->
                        </div>
                    </div>
                    <!--end::Menu item-->

                    <!--begin::Menu separator-->
                    <div class="separator my-2"></div>
                    <!--end::Menu separator-->

                    <!--begin::Menu item-->
                    <div class="menu-item px-5">
                        <a href="{{ url('/admin/profile') }}" class="menu-link px-5">My Profile</a>
                    </div>

                    <!--end::Menu item-->

                    <!--begin::Menu separator-->
                    <div class="separator my-2"></div>
                    <!--end::Menu separator-->

                    <!--begin::Menu item-->
                    <div class="menu-item px-5" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="left-start" data-kt-menu-offset="-15px, 0">
                        <a href="#" class="menu-link px-5">
                            <span class="menu-title position-relative">
                                Language
                                <span class="fs-8 rounded bg-light px-3 py-2 position-absolute translate-middle-y top-50 end-0">
                                    {{ session('locale') === 'ar' ? 'Arabic' : 'English' }}
                                    <img class="w-15px h-15px rounded-1 ms-2"
                                        src="{{ session('locale') === 'ar' ? asset('build-base/ktmt/media/flags/kuwait.svg') : asset('build-base/ktmt/media/flags/united-states.svg') }}"
                                        alt="Language Flag" />
                                </span>
                            </span>
                        </a>
                        <div class="menu-sub menu-sub-dropdown w-175px py-4">
                            <div class="menu-item px-3">
                                <a href="" class="menu-link d-flex px-5 {{ session('locale') === 'en' ? 'active' : '' }}">
                                    <span class="symbol symbol-20px me-4">
                                        <img class="rounded-1" src="{{ asset('build-base/ktmt/media/flags/united-states.svg') }}" alt="" />
                                    </span>
                                    English
                                </a>
                            </div>
                            <!--end::Menu item-->

                            <!--begin::Menu item-->
                            <div class="menu-item px-3">
                                <a href="" class="menu-link d-flex px-5 {{ session('locale') === 'ar' ? 'active' : '' }}">
                                    <span class="symbol symbol-20px me-4">
                                        <img class="rounded-1" src="{{ asset('build-base/ktmt/media/flags/kuwait.svg') }}" alt="" />
                                    </span>
                                    Arabic
                                </a>
                            </div>
                            <!--end::Menu item-->
                        </div>
                        <!--end::Menu sub-->
                    </div>
                    <!--end::Menu item-->

                    <!--begin::Menu item-->
                    <div class="menu-item px-5">
                        <a href="{{ route('user.logout')}}" class="menu-link px-5">Sign Out</a>
                    </div>
                    <!--end::Menu item-->
                </div>
                <!--end::User account menu-->
                <!--end::Menu wrapper-->
            </div>
            <!--end::User -->
            <!--begin::Header menu toggle-->
            <div class="d-flex align-items-stretch d-lg-none px-3 me-n3" title="Show header menu">
                <div class="topbar-item" id="kt_header_menu_mobile_toggle">
                    <i class="ki-outline ki-burger-menu-2 fs-1"></i>
                </div>
            </div>
            <!--end::Header menu toggle-->
        </div>
        <!--end::Toolbar wrapper-->
    </div>
    <!--end::Wrapper-->
</div>
<!--end::Container-->

@section('custom-js-section')
@endsection
