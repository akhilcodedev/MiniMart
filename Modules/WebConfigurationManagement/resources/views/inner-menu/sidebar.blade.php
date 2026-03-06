<div class="menubar">
    <!--begin::Menu-->
    <div class="menu menu-rounded menu-column menu-title-gray-700 menu-icon-gray-500 menu-arrow-gray-500 menu-bullet-gray-500 menu-arrow-gray-500 menu-state-bg fw-semibold w-250px"
        data-kt-menu="true">
        <!--begin::Menu item-->
        <div class="menu-item menu-sub-indention menu-accordion" data-kt-menu-trigger="click">
            <!--begin::Menu link-->
            <a href="#" class="menu-link py-3">
                <span class="menu-icon">
                    <i class="ki-duotone ki-chart-simple-2 fs-3"><span class="path1"></span><span
                            class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                </span>
                <span class="menu-title">System Configuration</span>
                <span class="menu-arrow"></span>
            </a>
            <!--end::Menu link-->

            <!--begin::Menu sub-->
            <div class="menu-sub menu-sub-accordion pt-3">
                <!--begin::Menu item-->
                <div class="menu-item">
                    <a href="{{ route('system.config.form', ['menu' => 'system-configuration', 'submenu' => 'timezone', 'form' => 'timezone-form']) }}"
                        class="menu-link py-3">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">Time Zone Configuration</span>
                    </a>
                </div>
                <!--end::Menu item-->

                <!--begin::Menu item-->
                <div class="menu-item">
                    <a href="{{ route('system.config.form', ['menu' => 'system-configuration', 'submenu' => 'datetime', 'form' => 'datetime-form']) }}"
                        class="menu-link py-3">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">Date, Time & Currency</span>
                    </a>
                </div>
                <!--end::Menu item-->

                <!--begin::Menu item-->
                <div class="menu-item">
                    <a href="{{ route('system.config.form', ['menu' => 'system-configuration', 'submenu' => 'languages', 'form' => 'languages-form']) }}"
                        class="menu-link py-3">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">Languages & Currencies</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a href="{{ route('system.config.form', ['menu' => 'system-configuration', 'submenu' => 'smtp', 'form' => 'smtp-form']) }}"
                        class="menu-link py-3">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">SMTP Configuration </span>
                    </a>
                </div>


                <div class="menu-item">
                    <a href="{{ route('system.config.form', ['menu' => 'system-configuration', 'submenu' => 'oss', 'form' => 'oss-form']) }}"
                        class="menu-link py-3">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">OSS Configuration</span>
                    </a>
                </div>

                <div class="menu-item">
                    <a href="{{ route('system.config.form', ['menu' => 'system-configuration', 'submenu' => 'otp', 'form' => 'otp-form']) }}"
                        class="menu-link py-3">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">OTP Configuration</span>
                    </a>
                </div>

                <div class="menu-item">

                    <a href="{{ route('system.config.form', ['menu' => 'system-configuration', 'submenu' => 'cart', 'form' => 'cart-form']) }}"

                       class="menu-link py-3">

                        <span class="menu-bullet">

                            <span class="bullet bullet-dot"></span>

                        </span>

                        <span class="menu-title">Cart Configuration </span>

                    </a>

                </div>

                <div class="menu-item">

                    <a href="{{ route('system.config.form', ['menu' => 'system-configuration', 'submenu' => 'mail', 'form' => 'test-mail']) }}"

                       class="menu-link py-3">

                        <span class="menu-bullet">

                            <span class="bullet bullet-dot"></span>

                        </span>

                        <span class="menu-title">Test Mail Configuration </span>

                    </a>

                </div>

                <!--end::Menu item-->
            </div>
            <!--end::Menu sub-->
        </div>

        <div class="menu-item menu-link-indention menu-accordion" data-kt-menu-trigger="click">
            <!--begin::Menu link-->
            <a href="#" class="menu-link py-3">
                <span class="menu-icon">
                    <i class="ki-duotone ki-calendar-2 fs-3"><span class="path1"></span><span class="path2"></span><span
                            class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                </span>
                <span class="menu-title">Customer Configuration</span>
                <span class="menu-arrow"></span>
            </a>
            <!--end::Menu link-->

            <!--begin::Menu sub-->
            <div class="menu-sub menu-sub-accordion pt-3">
                <!--begin::Menu item-->
                <div class="menu-item">
                    <a href="{{ route('system.config.form', ['menu' => 'customer-configuration', 'submenu' => 'support', 'form' => 'customer-support-form']) }}"
                        class="menu-link py-3">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">Customer Support</span>
                    </a>
                </div>
                <!--end::Menu item-->
            </div>
            <!--end::Menu sub-->
        </div>



        <div class="menu-item menu-link-indention menu-accordion" data-kt-menu-trigger="click">
            <!--begin::Menu link-->
            <a href="#" class="menu-link py-3">
                <span class="menu-icon">
                    <i class="fas fa-file-invoice-dollar fs-3"><span class="path1"></span><span
                            class="path2"></span><span class="path3"></span><span class="path4"></span><span
                            class="path5"></span></i>
                </span>
                <span class="menu-title">Tax Configuration</span>
                <span class="menu-arrow"></span>
            </a>
            <!--end::Menu link-->

            <!--begin::Menu sub-->
            <div class="menu-sub menu-sub-accordion pt-3">
                <!--begin::Menu item-->
                <div class="menu-item">
                    <a href="{{ route('system.config.form', ['system-configuration', 'tax-configuration', 'tax-support-form']) }}"
                        class="menu-link py-3">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">Tax Support</span>
                    </a>
                </div>
                <!--end::Menu item-->
            </div>
            <!--end::Menu sub-->
        </div>


        <!--end::Menu item-->
    </div>
    <!--end::Menu-->
    <div class="menu-content">
    </div>
</div>
