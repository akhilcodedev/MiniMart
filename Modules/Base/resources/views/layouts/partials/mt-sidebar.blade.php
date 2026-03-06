@php
    // Centralized user and super admin check
    $user = auth()->user();
    // Assuming 'is_super_admin' is a reliable attribute (value 1).
    // Alternatively, use Spatie roles: $isSuperAdmin = $user && $user->hasRole('super-admin');
    $isSuperAdmin = $user && $user->is_super_admin == 1;
@endphp

<div id="kt_aside" class="aside aside-dark aside-hoverable" data-kt-drawer="true" data-kt-drawer-name="aside"
     data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true"
     data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="start"
     data-kt-drawer-toggle="#kt_aside_mobile_toggle">
    <div class="aside-logo flex-column-auto" id="kt_aside_logo">
        <a href="{{ url('/') }}">
            <img alt="{{ config('app.name') }}" src="{{ asset('logo.svg') }}" class=" logo"
                 style="border-radius: 5px;height: 45px;width: 150px;background-color: aliceblue;" />
        </a>
        <div id="kt_aside_toggle" class="btn btn-icon w-auto px-0 btn-active-color-primary aside-toggle me-n2"
             data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body"
             data-kt-toggle-name="aside-minimize">
            <i class="ki-outline ki-double-left fs-1 rotate-180"></i>
        </div>
    </div>
    <div class="aside-menu flex-column-fluid">
        <div class="hover-scroll-overlay-y" id="kt_aside_menu_wrapper" data-kt-scroll="true"
             data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto"
             data-kt-scroll-dependencies="#kt_aside_logo, #kt_aside_footer" data-kt-scroll-wrappers="#kt_aside_menu"
             data-kt-scroll-offset="0">
            <div class="menu menu-column menu-title-gray-800 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500"
                 id="#kt_aside_menu" data-kt-menu="true">
                <br>

                {{-- Dashboard --}}
                {{-- Add specific permission if needed, e.g., 'view_dashboard'. Assuming all auth users can view for now --}}
                @if(auth()->check())
                    <div class="menu-item">
                        <a class="menu-link" href="{{ url('/') }}">
                        <span class="menu-bullet">
                            <i class="fas fa-tachometer-alt"></i> <!-- Dashboard Icon -->
                        </span>
                            <span class="menu-title">{{__('Dashboard')}}</span>
                        </a>
                    </div>
                @endif

                {{-- Users & Permissions Group (Mapped to user_permissions & user_management modules) --}}
                @if ($isSuperAdmin || $user->canAny(['list_user', 'list_role', 'list_permissions_role', 'assign_users_role', 'list_vendor', 'assign_vendor_to_users']))
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="ki-outline ki-key fs-2"></i>
                            </span>
                            <span class="menu-title">{{ __('Users & Permissions') }}</span> {{-- Consolidating user+vendor+roles --}}
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            {{-- User List (from user_management module) --}}
                            @if($isSuperAdmin || $user->can('list_user'))
                                <div class="menu-item">
                                    <a class="menu-link" href="{{ route('user.index') }}"> {{-- Update route if needed --}}
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('Users') }}</span> {{-- Changed to Plural --}}
                                    </a>
                                </div>
                            @endif

                            {{-- Vendor List (from user_management module) --}}
                            {{-- Uncomment if needed and visible in this menu
                            @if($isSuperAdmin || $user->can('list_vendor'))
                                <div class="menu-item">
                                    <a class="menu-link" href="{{ url('vendors') }}"> // Update URL/Route
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('Vendors') }}</span>
                                    </a>
                                </div>
                            @endif
                            --}}

                            {{-- Roles List (from user_permissions module) --}}
                            @if($isSuperAdmin || $user->can('list_role'))
                                <div class="menu-item">
                                    <a class="menu-link" href="{{ url('roles') }}"> {{-- Ensure this route matches --}}
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('Roles') }}</span>
                                    </a>
                                </div>
                            @endif
                            {{-- Permissions to Role (from user_permissions module) --}}
                            @if($isSuperAdmin || $user->can('list_permissions_role')) {{-- Changed permission name --}}
                            <div class="menu-item">
                                <a class="menu-link" href="{{ route('roles.assign-permissions.index') }}"> {{-- Ensure this route matches --}}
                                    <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                    <span class="menu-title">{{ __('Permissions to Role') }}</span>
                                </a>
                            </div>
                            @endif
                            {{-- Users to Roles (from user_permissions module) --}}
                            @if($isSuperAdmin || $user->can('assign_users_role')) {{-- Check if view needs 'list_users_role' or 'assign_users_role' --}}
                            <div class="menu-item">
                                <a class="menu-link" href="{{ route('users.assign-roles') }}"> {{-- Ensure this route matches --}}
                                    <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                    <span class="menu-title">{{ __('Users to Roles') }}</span>
                                </a>
                            </div>
                            @endif

                            {{-- Assign vendor to users (from user_management module) --}}
                            {{-- Uncomment if needed and visible in this menu
                            @if($isSuperAdmin || $user->can('assign_vendor_to_users'))
                                <div class="menu-item">
                                    <a class="menu-link" href="{{ url('assign-vendor-user') }}"> // Update URL/Route
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('Assign Vendor to Users') }}</span>
                                    </a>
                                </div>
                            @endif
                             --}}
                        </div>
                    </div>
                @endif

                {{-- Categories Group --}}
                @if ($isSuperAdmin || $user->canAny(['list_categories']))
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="ki-outline ki-category fs-2"></i>
                            </span>
                            <span class="menu-title">{{ __('Categories') }}</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            {{-- Category List --}}
                            @if($isSuperAdmin || $user->can('list_categories'))
                                <div class="menu-item">
                                    <a class="menu-link" href="{{ url('categories') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('Category') }}</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Product Group --}}
                @if ($isSuperAdmin || $user->canAny(['list_products', 'list_product_reviews', 'list_product_review_attributes']))
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="ki-outline ki-basket fs-2"></i>
                            </span>
                            <span class="menu-title">{{ __('Product') }}</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            {{-- Product List --}}
                            @if($isSuperAdmin || $user->can('list_products'))
                                <div class="menu-item">
                                    <a class="menu-link" href="{{ url('products') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('Product') }}</span>
                                    </a>
                                </div>
                            @endif
                            {{-- Product Review List --}}
                            @if($isSuperAdmin || $user->can('list_product_reviews'))
                                <div class="menu-item">
                                    <a class="menu-link" href="{{ route('products_review.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('Product Review') }}</span>
                                    </a>
                                </div>
                            @endif
                            {{-- Product Review Attributes List --}}
                            @if($isSuperAdmin || $user->can('list_product_review_attributes'))
                                <div class="menu-item">
                                    <a class="menu-link" href="{{ route('product_review_attributes.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('Product Review Attributes') }}</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Attributes Group --}}
                @if ($isSuperAdmin || $user->canAny(['show_attribute', 'show_attribute_set'])) {{-- Changed list_* to show_* --}}
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="fa-solid fa-tags fs-2"></i>
                            </span>
                            <span class="menu-title">{{ __('Attributes') }}</span>
                            <span class="menu-arrow"></span>
                        </span>
                    <div class="menu-sub menu-sub-accordion">
                        {{-- Attributes List --}}
                        @if($isSuperAdmin || $user->can('show_attribute')) {{-- Changed permission --}}
                        <div class="menu-item">
                            <a class="menu-link" href="{{ route('product.attributes.index') }}"> {{-- Route name based on blade file --}}
                                <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                <span class="menu-title">{{__('Attributes')}}</span>
                            </a>
                        </div>
                        @endif
                        {{-- Attribute Sets List --}}
                        @if($isSuperAdmin || $user->can('show_attribute_set')) {{-- Changed permission --}}
                        <div class="menu-item">
                            <a class="menu-link" href="{{ route('product.attribute.sets.index') }}"> {{-- Route name based on blade file --}}
                                <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                <span class="menu-title">{{ __('Attribute Sets') }}</span>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- General Group --}}
                @if ($isSuperAdmin || $user->canAny(['list_email_templates', 'list_banners']))
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="fa-solid fa-gear fs-2"></i>
                            </span>
                            <span class="menu-title">{{ __('General') }}</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            {{-- E-mail Template List --}}
                            @if($isSuperAdmin || $user->can('list_email_templates'))
                                <div class="menu-item">
                                    <a class="menu-link" href="{{ route('email.templates.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{__('E-mail Template')}}</span>
                                    </a>
                                </div>
                            @endif
                            {{-- Hero Banner List --}}
                            @if($isSuperAdmin || $user->can('list_banners'))
                                <div class="menu-item">
                                    <a class="menu-link" href="{{ route('banners.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('Hero Banner') }}</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- CMS Group --}}
                @if ($isSuperAdmin || $user->canAny(['list_cms_pages', 'list_cms_blocks']))
                    <div data-kt-menu-trigger="click" class="menu-item  menu-accordion">
                    <span class="menu-link">
                        <span class="menu-icon">
                            <i class="ki-outline ki-document fs-2"></i>
                        </span>
                        <span class="menu-title">{{__('CMS')}}</span>
                        <span class="menu-arrow"></span>
                    </span>
                        <div class="menu-sub menu-sub-accordion">
                            {{-- CMS Pages --}}
                            @if ($isSuperAdmin || $user->can('list_cms_pages'))
                                <div class="menu-item">
                                    <a class="menu-link" href="{{ route('cms.pages') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                        <span class="menu-title">{{__('Pages')}}</span>
                                    </a>
                                </div>
                            @endif
                            {{-- CMS Blocks --}}
                            @if ($isSuperAdmin || $user->can('list_cms_blocks'))
                                <div class="menu-item">
                                    <a class="menu-link" href="{{ route('cms-blocks.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                        <span class="menu-title">{{__('Blocks')}}</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Marketing Group (Combined Catalog + Cart Rules) --}}
                @if ($isSuperAdmin || $user->canAny(['list_catalog_rules', 'show_coupons', 'show_coupon_modes', 'show_coupon_types', 'show_coupon_entities']))
                    <div data-kt-menu-trigger="click" class="menu-item  menu-accordion">
                    <span class="menu-link">
                        <span class="menu-icon">
                            <i class="ki-solid ki-rocket fs-2"></i>
                        </span>
                        <span class="menu-title">{{__('Marketing')}}</span>
                        <span class="menu-arrow"></span>
                    </span>
                        <div class="menu-sub menu-sub-accordion">
                            {{-- Catalog Price Rule --}}
                            @if ($isSuperAdmin || $user->can('list_catalog_rules'))
                                <div class="menu-item">
                                    <a class="menu-link" href="{{ route('catalog-price-rules.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                        <span class="menu-title">{{__('Catalog Price Rule')}}</span>
                                    </a>
                                </div>
                            @endif
                            {{-- Cart Price Rules --}}
                            @if ($isSuperAdmin || $user->can('show_coupons')) {{-- Changed permission --}}
                            <div class="menu-item">
                                <a class="menu-link" href="{{ route('priceRule.cart.coupons.index') }}"> {{-- Route based on blade --}}
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">{{__('Cart Price Rules')}}</span>
                                </a>
                            </div>
                            @endif
                            {{-- Coupon Modes --}}
                            @if ($isSuperAdmin || $user->can('show_coupon_modes')) {{-- Changed permission --}}
                            <div class="menu-item">
                                <a class="menu-link" href="{{ route('priceRule.cart.couponModes.index') }}"> {{-- Route based on blade --}}
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">{{ __('Coupon Modes') }}</span>
                                </a>
                            </div>
                            @endif
                            {{-- Coupon Types --}}
                            @if ($isSuperAdmin || $user->can('show_coupon_types')) {{-- Changed permission --}}
                            <div class="menu-item">
                                <a class="menu-link" href="{{ route('priceRule.cart.couponTypes.index') }}"> {{-- Route based on blade --}}
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">{{ __('Coupon Types') }}</span>
                                </a>
                            </div>
                            @endif
                            {{-- Coupon Entities --}}
                            @if ($isSuperAdmin || $user->can('show_coupon_entities')) {{-- Changed permission --}}
                            <div class="menu-item">
                                <a class="menu-link" href="{{ route('priceRule.cart.couponEntities.index') }}"> {{-- Route based on blade --}}
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">{{ __('Coupon Entities') }}</span>
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Customer Management Group --}}
                {{-- Using permissions from customer_management module --}}
                @if ($isSuperAdmin || $user->canAny(['list_customer', 'customer_group_list']))
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="fa-solid fa-user fs-2"></i>
                            </span>
                            <span class="menu-title">{{ __('Customer Management') }}</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            {{-- Customer List --}}
                            @if($isSuperAdmin || $user->can('list_customer'))
                                <div class="menu-item">
                                    <a class="menu-link" href="{{ url('/customer') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                        <span class="menu-title">{{ __('Customers') }}</span>
                                    </a>
                                </div>
                            @endif
                            {{-- Customer Group List --}}
                            @if($isSuperAdmin || $user->can('customer_group_list'))
                                <div class="menu-item">
                                    <a class="menu-link" href="{{ url('/customers/groups/index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('Customers Group') }}</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Store Management Group --}}
                @if ($isSuperAdmin || $user->canAny(['list_stores']))
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="fa-solid fa-store fs-2"></i>
                            </span>
                            <span class="menu-title">{{ __('Store Management') }}</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            {{-- Stores List --}}
                            @if ($isSuperAdmin || $user->can('list_stores'))
                                {{-- Wrap the direct link in menu-item if needed for styling/JS --}}
                                <div class="menu-item">
                                    <a class="menu-link" href="{{ url('/stores/index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('Stores') }}</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif


                {{-- Order Management Group --}}
                @if ($isSuperAdmin || $user->canAny(['list_orders']))
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="fa-solid fa-boxes-stacked fs-2"></i>
                            </span>
                            <span class="menu-title">{{ __('Order Management') }}</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            {{-- Orders List --}}
                            @if ($isSuperAdmin || $user->can('list_orders'))
                                <div class="menu-item">
                                    <a class="menu-link" href="{{ route('ordermanagement.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('Orders') }}</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif


                {{-- Shipping Management Group --}}
                @if ($isSuperAdmin || $user->canAny(['list_shipping_methods']))
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="fa-solid fa-truck fs-2"></i>
                            </span>
                            <span class="menu-title">{{ __('Shipping Management') }}</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            {{-- Shipping Method List --}}
                            @if ($isSuperAdmin || $user->can('list_shipping_methods'))
                                <div class="menu-item">
                                    <a class="menu-link" href="{{ route('shipping.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('Shipping Method') }}</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Payment Management Group --}}
                @if ($isSuperAdmin || $user->canAny(['list_payment_methods']))
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="fa-solid fa-credit-card fs-2"></i>
                            </span>
                            <span class="menu-title">{{ __('Payment Management') }}</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            {{-- Payment Method List --}}
                            @if ($isSuperAdmin || $user->can('list_payment_methods'))
                                <div class="menu-item">
                                    <a class="menu-link" href="{{ route('payment.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('Payment Method') }}</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Tax Management Group --}}
                @if ($isSuperAdmin || $user->canAny(['list_tax_methods', 'list_tax_rates']))
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="fa-solid fa-file-invoice-dollar fs-2"></i>
                            </span>
                            <span class="menu-title">{{ __('Tax Management') }}</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            {{-- Tax Method List --}}
                            @if ($isSuperAdmin || $user->can('list_tax_methods'))
                                <div class="menu-item">
                                    <a class="menu-link" href="{{ route('tax.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('Tax Method') }}</span>
                                    </a>
                                </div>
                            @endif
                            {{-- Tax Rate List --}}
                            @if ($isSuperAdmin || $user->can('list_tax_rates'))
                                <div class="menu-item">
                                    <a class="menu-link" href="{{ route('tax-rates.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">{{ __('Tax Rate') }}</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Web Configuration Management --}}
                @if ($isSuperAdmin || $user->can('manage_configuration'))
                    <div class="menu-item">
                        <a class="menu-link" href="{{ url('/configure') }}">
                            <span class="menu-icon">
                                <i class="fa-solid fa-gears fs-2"></i>
                            </span>
                            <span class="menu-title">{{ __('Web Configuration Management') }}</span>
                        </a>
                    </div>
                @endif

                {{-- Newsletter Management --}}
                @if ($isSuperAdmin || $user->can('list_newsletters'))
                    <div class="menu-item">
                        <a class="menu-link" href="{{ route('newsletters.index') }}">
                            <span class="menu-icon">
                                <i class="fa-solid fa-newspaper fs-2"></i>
                            </span>
                            <span class="menu-title">{{ __('Newsletter Management') }}</span>
                        </a>
                    </div>
                @endif

                {{-- URL Rewrites --}}
                @if ($isSuperAdmin || $user->can('list_url_rewrites'))
                    <div class="menu-item">
                        <a class="menu-link" href="{{ route('urlrewrite.index') }}">
                            <span class="menu-icon">
                                <i class="fa-solid fa-link fs-2"></i>
                            </span>
                            <span class="menu-title">{{ __('URL Rewrites') }}</span>
                        </a>
                    </div>
                @endif


                @if ($isSuperAdmin || $user->can('list_hot_deals'))
                <div class="menu-item">
                    <a class="menu-link" href="{{ route('hot_deals.index') }}">
                        <span class="menu-icon">
                            <i class="fa-solid fa-fire fs-2 "></i>
                        </span>
                        <span class="menu-title">{{ __('HotDeals') }}</span>
                    </a>
                </div>
                @endif


            </div> {{-- End #kt_aside_menu --}}
        </div> {{-- End #kt_aside_menu_wrapper --}}
    </div> {{-- End .aside-menu --}}


    <div class="aside-footer flex-column-auto pb-7 px-5" id="kt_aside_footer">
        {{-- Footer content if any --}}
    </div>
</div> {{-- End #kt_aside --}}

{{-- Keep the style block --}}
<style>
    .aside-menu .menu-item.menu-accordion.showing>.menu-link,
    .aside-menu .menu-item.menu-accordion.show>.menu-link,
    .aside-menu .menu-item.menu-accordion.active>.menu-link {
        transition: color .2s ease;
        background-color: #1b1b28;
        color: #fff;
    }
    /* Style adjustments if direct links under accordion need fixing */
    .menu-sub-accordion > .menu-item > .menu-link {
        /* Add styles if needed, e.g., */
        /* padding-left: 2.5rem; */
    }
</style>
