@extends('base::layouts.mt-main')
@section('content')
    <style>
        .image-input-placeholder {
            background-image: url("https://preview.keenthemes.com/html/metronic/docs/assets/media/svg/avatars/blank.svg");
        }

        [data-bs-theme="dark"] .image-input-placeholder {
            background-image: url("https://preview.keenthemes.com/html/metronic/docs/assets/media/svg/avatars/blank.svg");
        }
    </style>

    {{--    <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">--}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" rel="stylesheet"/>

    <div class="container">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Form-->
            <form id="kt_ecommerce_update_product_form" class="form d-flex flex-column flex-lg-row"
                  action="{{ route('products.update',$product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <!--begin::Aside column-->
                <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">

                    <div class="card card-flush py-4">
                        <div class="card-header">
                            <!--begin::Card title-->
                            <div class="card-title">
                                <h2>Prodcut Type</h2>
                            </div>

                        </div>
                        <div class="card-body pt-0">
                            <!--begin::Select2-->
                            <select class="form-select form-select-solid" data-control="select2"
                                    data-hide-search="true"
                                    name="product_type_id"
                                    data-placeholder="Product Type"
                                    data-kt-ecommerce-product-filter="Product Type">
                                <option value="1">Simple Product</option>
                                @foreach ($productTypes as $type)
                                    <option value="{{ $type->id }}" {{ $product->productType->id == $type->id ? 'selected' : '' }}>{{ ucwords($type->name) }}</option>
                                @endforeach

                            </select>

                            <div class="d-none mt-10">
                                <label for="kt_ecommerce_add_product_status_datepicker" class="form-label">Select
                                    publishing date and time</label>
                                <input class="form-control" id="kt_ecommerce_add_product_status_datepicker"
                                       placeholder="Pick date & time"/>
                            </div>
                            <!--end::Datepicker-->
                        </div>
                        <!--begin::Card header-->
                        <div class="card-header">
                            <!--begin::Card title-->
                            <div class="card-title">
                                <h2>Prodcut Status</h2>
                            </div>

                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!--begin::Select2-->
                            <select class="form-select mb-2" data-control="select2"
                                    data-hide-search="true"
                                    name="product_status"
                                    data-placeholder="Select an option"
                                    id="kt_ecommerce_add_product_stock_tatus">
                                <option></option>
                                <option value="active" {{ $product->status == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive"{{ $product->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            <!--end::Select2-->
                            <!--begin::Description-->
                            <div class="text-muted fs-7">Set the product status.</div>
                            <!--end::Description-->
                            <!--begin::Datepicker-->
                            <div class="d-none mt-10">
                                <label for="kt_ecommerce_add_product_status_datepicker" class="form-label">Select
                                    publishing date and time</label>
                                <input class="form-control" id="kt_ecommerce_add_product_status_datepicker"
                                       placeholder="Pick date & time"/>
                            </div>
                            <!--end::Datepicker-->
                        </div>
                        <!--end::Card body-->

                        <!--begin::Card header-->
                        <div class="card-header">
                            <!--begin::Card title-->
                            <div class="card-title">
                                <h2>Stock Status</h2>
                            </div>

                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!--begin::Select2-->
                            <select class="form-select mb-2" data-control="select2"
                                    data-hide-search="true"
                                    name="stock_status"
                                    data-placeholder="Select an option"
                                    id="kt_ecommerce_add_product_status_select">
                                <option></option>
                                <option value="1"{{ $product->is_in_stock == '1' ? 'selected' : '' }}>In Stock</option>
                                <option value="0"{{ $product->is_in_stock == '0' ? 'selected' : '' }}>Out Of Stock</option>
                            </select>
                            <!--end::Select2-->
                            <!--begin::Description-->
                            <div class="text-muted fs-7">Set the product status.</div>
                            <!--end::Description-->
                            <!--begin::Datepicker-->
                            <div class="d-none mt-10">
                                <label for="kt_ecommerce_add_product_status_datepicker" class="form-label">Select
                                    publishing date and time</label>
                                <input class="form-control" id="kt_ecommerce_add_product_status_datepicker"
                                       placeholder="Pick date & time"/>
                            </div>
                            <!--end::Datepicker-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Status-->
                    <!--begin::Category & tags-->
                    <div class="card card-flush py-4">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <!--begin::Card title-->
                            <div class="card-title">
                                <h2>Product Details</h2>
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!--begin::Input group-->
                            <!--begin::Label-->
                            <label class="form-label">Categories</label>
                            <!--end::Label-->
                            <!--begin::Select2-->
                            <div id="category-tree" class="card mt-10 p-5 mw-1200px"></div>
                            <input type="hidden" id="selected_category" name="selected_category">
                            <!--end::Select2-->
                            <!--begin::Description-->
                            <div class="text-muted fs-7 mb-7">Add product to a category.</div>
                            <!--end::Description-->
                            <!--end::Input group-->
                            <!--begin::Button-->
                            <a href="apps/ecommerce/catalog/add-category.html"
                               class="btn btn-light-primary btn-sm mb-10">
                                <i class="ki-outline ki-plus fs-2"></i>Create new category</a>
                            <!--end::Button-->

                            <!--begin::Input group for Language Selection-->
                            <!--begin::Label-->
                            <label class="form-label">Language</label>
                            <!--end::Label-->
                            <!--begin::Select2 for Languages-->
                            <select class="form-select mb-2" data-control="select2" data-placeholder="Select language" name="language_id">
                                <option></option>
                                @foreach ($languages as $language)
                                    <option value="{{ $language->id }}"
                                            @if (isset($product->metaDetails) && $product->metaDetails->contains('language_id', $language->id))
                                            selected
                                        @endif>
                                        {{ $language->name }}
                                    </option>
                                @endforeach
                            </select>

                            <!--end::Select2 for Languages-->
                            <!--begin::Description-->
                            <div class="text-muted fs-7 mb-7">Select the language for the product's meta details.</div>
                            <!--end::Description-->
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <!--begin::Label-->
                            <label class="form-label d-block">Tags</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input id="kt_ecommerce_add_product_tags" name="kt_ecommerce_add_product_tags"
                                   class="form-control mb-2" value=""/>
                            <!--end::Input-->
                            <!--begin::Description-->
                            <div class="text-muted fs-7">Add tags to a product.</div>
                            <!--end::Description-->
                            <!--end::Input group-->
                        </div>
                        <!--end::Card body-->
                    </div>

                </div>
                <!--end::Aside column-->
                <!--begin::Main column-->
                <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                    <!--begin:::Tabs-->
                    <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-n2">
                        <!--begin:::Tab item-->
                        <li class="nav-item">
                            <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab"
                               href="#kt_ecommerce_add_product_general">General</a>
                        </li>
                        <!--end:::Tab item-->
                        <!--begin:::Tab item-->
                        <li class="nav-item">
                            <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab"
                               href="#kt_ecommerce_add_product_advanced">Advanced</a>
                        </li>
                        <!--end:::Tab item-->
                        <!--begin:::Tab item-->
                        <li class="nav-item">
                            <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab"
                               href="#kt_ecommerce_add_product_attributes">Attributes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab"
                               href="#kt_ecommerce_add_product_related_products">Related Products</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab"
                               href="#kt_ecommerce_add_product_cross_selling_products">Cross Selling Products</a>
                        </li>
                        <!--end:::Tab item-->
                    </ul>
                    <!--end:::Tabs-->
                    <!--begin::Tab content-->
                    <div class="tab-content">
                        <!--begin::Tab pane-->
                        <div class="tab-pane fade show active" id="kt_ecommerce_add_product_general" role="tab-panel">
                            <div class="d-flex flex-column gap-7 gap-lg-10">
                                <!--begin::General options-->
                                <div class="card card-flush py-4">
                                    <!--begin::Card header-->
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>General</h2>
                                        </div>
                                    </div>
                                    <!--end::Card header-->
                                    <!--begin::Card body-->
                                    <div class="card-body pt-0">
                                        <!--begin::Input group-->
                                        <div class="mb-10 fv-row">
                                            <!--begin::Label-->
                                            <label class="required form-label">Product Name</label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="text" name="product_name" class="form-control mb-2"
                                                   placeholder="Product name" value="{{ $product->name }}"/>
                                            <!--end::Input-->
                                            <!--begin::Description-->
                                            <div class="text-muted fs-7">A product name is required and recommended to
                                                be unique.
                                            </div>
                                            <!--end::Description-->
                                        </div>
                                        <div class="mb-10 fv-row">
                                            <!--begin::Label-->
                                            <label class="required form-label">URL Key</label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="text" name="url_key" class="form-control mb-2" placeholder="URL Key" value="{{ $product->url_key }}" />
                                            <!--end::Input-->
                                            <!--begin::Description-->
                                            <div class="text-muted fs-7">A url-key is required and recommended to be unique.</div>
                                            <!--end::Description-->
                                        </div>
                                        <!--end::Input group-->
                                        <!--begin::Input group-->
                                        <div>
                                            <!--begin::Label-->
                                            <label class="form-label">Short Description</label>
                                            <!--end::Label-->
                                            <!--begin::Editor-->

                                            <textarea id="short_description"
                                                      name="short_description">{{ $product->storeProduct ? $product->storeProduct->short_description : ''}}</textarea>
                                            <!--end::Editor-->
                                            <!--begin::Description-->
                                            <div class="text-muted fs-7">Set a short description to the product for
                                                better visibility.
                                            </div>
                                            <!--end::Description-->
                                        </div>
                                        <!--end::Input group-->

                                        <!--begin::Input group-->
                                        <div>
                                            <!--begin::Label-->
                                            <label class="form-label">Description</label>
                                            <!--end::Label-->
                                            <!--begin::Editor-->

                                            <textarea id="description"
                                                      name="description">{{ $product->storeProduct ? $product->storeProduct->description : '' }}</textarea>

                                            <!--end::Editor-->
                                            <!--begin::Description-->
                                            <div class="text-muted fs-7">Set a description to the product for better
                                                visibility.
                                            </div>
                                            <!--end::Description-->
                                        </div>
                                        <!--end::Input group-->


                                    </div>
                                    <!--end::Card header-->
                                </div>
                                <!--end::General options-->
                                <!--begin::Media-->
                                <div class="card card-flush py-4">
                                    <!--begin::Card header-->
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>Media</h2>
                                        </div>
                                    </div>
                                    <!--end::Card header-->
                                    <!--begin::Card body-->
                                    <div class="card-body pt-0">
                                        <!--begin::Input group-->
                                        <div class="fv-row mb-2">
                                            <label>Existing Images</label>
                                            @foreach ($product->productImages as $image)
                                                <div class="d-flex align-items-center mb-2">
                                                    <!-- Display Image -->
                                                    <img src="{{ asset($image->image_url) }}" alt="Product Image" width="100" class="me-2">

                                                    <!-- Delete Checkbox -->
                                                    <input type="checkbox" name="delete_product_image_ids[]" value="{{ $image->id }}" class="me-2">
                                                    <label for="delete_product_image_ids[]">Delete</label>

                                                    <!-- Product Image Type Selection (Small Size) -->
                                                    <select name="product_image_type[{{ $image->id }}]" class="form-select form-select-sm ms-2">
                                                        <!-- Default 'Select Type' option -->
                                                        <option value="" disabled {{ is_null($image->image_type_id) ? 'selected' : '' }}>Select Type</option>

                                                        @foreach ($productImageType as $type)
                                                            <option value="{{ $type->id }}" {{ $image->image_type_id == $type->id ? 'selected' : '' }}>
                                                            {{ $type->name }} <!-- Assuming 'name' holds the type name -->
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                        @endforeach

                                        <!-- New Image Upload -->
                                            <input type="file" class="form-control mb-2" name="product_image[]" id="kt_ecommerce_add_product_image" multiple>
                                        </div>
                                        <!--end::Input group-->
                                        <!--begin::Description-->
                                        <div class="text-muted fs-7">Set the product media gallery.</div>
                                        <!--end::Description-->
                                    </div>
                                </div>

                                <!--end::Card header-->
                                <!--end::Media-->
                                <!--begin::Pricing-->
                                <div class="card card-flush py-4">
                                    <!--begin::Card header-->
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>Pricing</h2>
                                        </div>
                                    </div>
                                    <!--end::Card header-->
                                    <!--begin::Card body-->
                                    <div class="card-body pt-0">
                                        <!--begin::Input group-->
                                        <div class="mb-10 fv-row">
                                            <!--begin::Label-->
                                            <label class="required form-label">Base Price</label>
                                            <input type="text" name="price" class="form-control mb-2"
                                                   placeholder="Product price" value="{{ $product->price }}"/>
                                            <div class="text-muted fs-7">Set the product price.</div>

                                            <label class="required form-label">Special Price</label>

                                            <input type="text" name="special_price" class="form-control mb-2" placeholder="Special price" value="{{$product->special_price}}" />

                                            <div class="text-muted fs-7">Set the product Special price.</div>

                                            <label class="form-label">Special price valid up to</label>


                                            <input class="form-control" placeholder="Pick date rage" name="special_price_date" id="special_price_date"
                                                   value=""/>

                                        </div>
                                        <div class="d-flex flex-column text-center mb-5"  style="display: none;">
                                            <div class="d-flex align-items-start justify-content-center mb-7">
                                                    <span class="fw-bold fs-3x"
                                                          id="kt_ecommerce_add_product_discount_label" style="display: none;"></span>
                                                <span class="fw-bold fs-4 mt-1 ms-2"></span>
                                            </div>
                                            <div id="kt_ecommerce_add_product_discount_slider" class="noUi-sm"
                                                 style="display: none;"></div>
                                        </div>

                                    </div>
                                    <!--end::Card header-->
                                </div>
                                <!--end::Pricing-->
                            </div>
                        </div>
                        <!--end::Tab pane-->
                        <!--begin::Tab pane-->
                        <div class="tab-pane fade" id="kt_ecommerce_add_product_advanced" role="tab-panel">
                            <div class="d-flex flex-column gap-7 gap-lg-10">
                                <!--begin::Inventory-->
                                <div class="card card-flush py-4">
                                    <!--begin::Card header-->
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>Inventory</h2>
                                        </div>
                                    </div>
                                    <!--end::Card header-->
                                    <!--begin::Card body-->
                                    <div class="card-body pt-0">
                                        <!--begin::Input group-->
                                        <div class="mb-10 fv-row">
                                            <!--begin::Label-->
                                            <label class="required form-label">SKU</label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="text" name="sku" class="form-control mb-2"
                                                   placeholder="SKU Number" value="{{ $product->sku }}"/>
                                            <!--end::Input-->
                                            <!--begin::Description-->
                                            <div class="text-muted fs-7">Enter the product SKU.</div>
                                            <!--end::Description-->
                                        </div>
                                        <!--end::Input group-->
                                        <!--begin::Input group-->

                                        <!--end::Input group-->
                                        <!--begin::Input group-->
                                        <div class="mb-10 fv-row">
                                            <!--begin::Label-->
                                            <label class="required form-label">Quantity</label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <div class="d-flex gap-3">
                                                <input type="number" name="quantity" class="form-control mb-2"
                                                       placeholder="Product Quantity" value="{{ $product->quantity }}"/>
                                                {{--                                                <input type="number" name="store" class="form-control mb-2" placeholder="In Store" />--}}
                                            </div>
                                            <!--end::Input-->
                                            <!--begin::Description-->
                                            <div class="text-muted fs-7">Enter the product quantity.</div>
                                            <!--end::Description-->
                                        </div>

                                    </div>
                                    <!--end::Card header-->
                                </div>
                                <!--end::Inventory-->
                                <!--begin::Variations-->

                                @if($product->is_variant == 0)

                                    <div class="card card-flush py-4">
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h2>Product Options</h2>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div data-kt-ecommerce-catalog-add-product="auto-options">
                                                <label class="form-label">Product Options</label>

                                                <div>
                                                    <a href="{{ route('product.variants.all', $product->id) }}"
                                                       class="btn btn-primary font-weight-bolder">
                                                        <i class="flaticon2-list-2 text-info"></i>View All Variants
                                                    </a>
                                                    <a href="{{ route('products.listAllAttributes', $product->id ) }}"
                                                       class="btn btn-primary font-weight-bolder">
                                                        <i class="flaticon2-list-2 text-info"></i>All Attributes
                                                    </a>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                            @endif
                                <!--begin::Meta options-->
                                <div class="card card-flush py-4">
                                    <!--begin::Card header-->
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>Meta Options</h2>
                                        </div>
                                    </div>
                                    <!--end::Card header-->
                                    <!--begin::Card body-->
                                    <div class="card-body pt-0">
                                        <!--begin::Input group-->
                                        <div class="mb-10">
                                            <!--begin::Label-->
                                            <label class="form-label">Meta Tag Title</label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="text" class="form-control mb-2" name="meta_title"
                                                   placeholder="Meta tag name" value="{{ $product->storeProduct ? $product->storeProduct->meta_title : ''}}"/>
                                            <!--end::Input-->
                                            <!--begin::Description-->
                                            <div class="text-muted fs-7">Set a meta tag title. Recommended to be simple
                                                and precise keywords.
                                            </div>
                                            <!--end::Description-->
                                        </div>
                                        <!--end::Input group-->
                                        <!--begin::Input group-->
                                        <div class="mb-10">
                                            <!--begin::Label-->
                                            <label class="form-label">Meta Tag Description</label>
                                            <!--end::Label-->
                                            <!--begin::Editor-->
                                            <!--end::Editor-->
                                            <textarea id="meta_description"
                                                      name="meta_description">{{ $product->storeProduct ? $product->storeProduct->meta_description : '' }}</textarea>
                                            <!--begin::Description-->
                                            <div class="text-muted fs-7">Set a meta tag description to the product for
                                                increased SEO ranking.
                                            </div>
                                            <!--end::Description-->
                                        </div>
                                        <!--end::Input group-->
                                        <!--begin::Input group-->
                                        <div>
                                            <!--begin::Label-->
                                            <label class="form-label">Meta Tag Keywords</label>
                                            <!--end::Label-->
                                            <!--begin::Editor-->
                                            <input id="kt_ecommerce_add_product_meta_keywords" name="meta_keywords"
                                                   class="form-control mb-2" value="{{ $product->storeProduct ? $product->storeProduct->meta_keyword : '' }}"/>

                                            <!--end::Editor-->
                                            <!--begin::Description-->
                                            <div class="text-muted fs-7">Set a list of keywords that the product is
                                                related to. Separate the keywords by adding a comma
                                                <code>,</code>between each keyword.
                                            </div>
                                            <!--end::Description-->
                                        </div>
                                        <div>
                                            <!--begin::Label-->
                                            <label class="form-label">Product Meta Image</label>
                                            <!--end::Label-->
                                            <!--begin::Editor-->
                                            <input type="file" class="form-control mb-2" name="meta_image"
                                                   id="kt_ecommerce_add_product_meta_keywords">



                                            <p>Current:</p>
                                            <img src="{{ asset($product->storeProduct ? $product->storeProduct->meta_image : '') }}" alt="Meta Image" width="100">
                                            <input type="checkbox" name="delete_product_meta_image"
                                                   value="{{ $product->storeProduct ? $product->storeProduct->meta_image : ''}}"> Delete




                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                    <!--end::Card header-->
                                </div>

                                <div class="card card-flush py-4">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>Product Stock Advanced</h2>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="mb-5 fv-row">
                                            <label class="form-label" for="out_of_stock_threshold">Out-Of-Stock Threshold</label>
                                            <div class="d-flex gap-3">
                                                <input type="number" name="out_of_stock_threshold" id="out_of_stock_threshold" class="form-control mb-2" placeholder="" value="{{ $product->out_of_stock_threshold ??  0}}" />
                                            </div>
                                        </div>

                                        <div class="mb-5 fv-row">
                                            <label class="form-label" for="min_qty_allowed_in_shopping_cart">Minimum Qty Allowed in Shopping Cart</label>
                                            <div class="d-flex gap-3">
                                                <input type="number" name="min_qty_allowed_in_shopping_cart" id="min_qty_allowed_in_shopping_cart" class="form-control mb-2" placeholder="" value="{{ $product->min_qty_allowed_in_shopping_cart ??  ''}}" />
                                            </div>
                                        </div>

                                        <div class="mb-5 fv-row">
                                            <label class="form-label" for="max_qty_allowed_in_shopping_cart">Maximum Qty Allowed in Shopping Cart</label>
                                            <div class="d-flex gap-3">
                                                <input type="number" name="max_qty_allowed_in_shopping_cart" id="max_qty_allowed_in_shopping_cart" class="form-control mb-2" placeholder="" value="{{ $product->max_qty_allowed_in_shopping_cart ??  ''}}"  />
                                            </div>
                                        </div>
                                        <div class="mb-5 fv-row">
                                            <label class="required form-label" for="qty_uses_decimals">Qty Uses Decimals</label>
                                            <div class="d-flex gap-3">
                                                <select class="form-select mb-2" data-control="select2"
                                                        data-hide-search="true"
                                                        name="qty_uses_decimals"
                                                        data-placeholder="Select an option"
                                                        id="qty_uses_decimals">
                                                    <option value="0" {{ isset($product->qty_uses_decimals) && $product->qty_uses_decimals == 0 ? 'selected' : '' }}>No</option>
                                                    <option value="1" {{ isset($product->qty_uses_decimals) && $product->qty_uses_decimals == 1 ? 'selected' : '' }}>Yes</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-5 fv-row">
                                            <label class="required form-label" for="backorders">Backorders</label>
                                            <div class="d-flex gap-3">
                                                <select class="form-select mb-2" data-control="select2"
                                                        data-hide-search="true"
                                                        name="backorders"
                                                        data-placeholder="Select an option"
                                                        id="backorders">
                                                    <option value="0" {{ isset($product->backorders) && $product->backorders == 0 ? 'selected' : '' }}>No Backorders</option>
                                                    <option value="1" {{ isset($product->backorders) && $product->backorders == 1 ? 'selected' : '' }}>Allow Qty Below 0</option>
                                                    <option value="2" {{ isset($product->backorders) && $product->backorders == 2 ? 'selected' : '' }}>Allow Qty Below 0 and Notify Customer</option>
                                                    <option value="3" {{ isset($product->backorders) && $product->backorders == 3 ? 'selected' : '' }}>Allow Pre-Order</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!--end::Meta options-->
                            </div>
                        </div>



                        <!--end::Tab pane-->
                        <!--begin::Tab pane-->
                        <div class="tab-pane fade" id="kt_ecommerce_add_product_attributes" role="tab-panel">
                            <div class="d-flex flex-column gap-7 gap-lg-10">

                                <!--begin::Attribute Set options-->
                                <div class="card card-flush py-4">
                                    <!--begin::Card header-->
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>Attribute Sets</h2>
                                        </div>
                                    </div>
                                    <!--end::Card header-->
                                    <!--begin::Card body-->
                                    <div class="card-body pt-0">

                                        <div class="mb-10 fv-row">
                                            <label class="required form-label" for="attribute_set_id">{{ __('Attribute Set') }}</label>
                                            <select name="attribute_set_id" id="attribute_set_id" class="form-control mb-2">
                                                <option value="">Please select Attribute Set</option>
                                                @foreach($attributeSets as $attributeSet)
                                                    <option value="{{ $attributeSet->id }}" data-set-id="{{ $attributeSet->id }}" data-set-name="{{ $attributeSet->name }}" @if($attributeSet->id == $product->attribute_set_id) selected @endif>
                                                        {{ $attributeSet->label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="text-muted fs-7">Select the Attribute Set.</div>
                                        </div>

                                    </div>
                                    <!--end::Card body-->
                                </div>
                                <!--end::Attribute Set options-->

                                <!--begin::Attribute options-->
                                <div class="card card-flush py-4">
                                    <!--begin::Card header-->
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>Attributes</h2>
                                        </div>
                                    </div>
                                    <!--end::Card header-->
                                    <!--begin::Card body-->
                                    <div class="card-body pt-0">

                                        <div class="row">
                                            <div class="col col-12">

                                                <div class="product_attributes_main_section">

                                                </div>

                                            </div>
                                        </div>

                                    </div>
                                    <!--end::Card body-->
                                </div>
                                <!--end::Attribute options-->

                            </div>
                        </div>
                        <!--end::Tab pane-->
                        <div class="tab-pane fade" id="kt_ecommerce_add_product_related_products" role="tab-panel">
                            <div class="d-flex flex-column gap-7 gap-lg-10">
                                <!--begin::Related Products-->
                                <div class="card card-flush py-4">
                                    <!--begin::Card header-->
                                    <input type="hidden" id="product_id"value="{{ $product->id }}">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>Related Products</h2>
                                            <button type="button" id="saveRelatedProducts" class="btn btn-primary btn-sm">Save </button>                                        </div>
                                        <div class="card-toolbar">
                                            <input type="text" id="related_product_search" class="form-control form-control-sm"
                                                   placeholder="Search by SKU or Name">
                                        </div>

                                    </div>
                                    <!--end::Card header-->

                                @include('products::partials.related_products_table')

                                <!--end::Card body-->
                                </div>
                                <!--end::Related Products-->
                            </div>
                        </div>
                        <div class="tab-pane fade" id="kt_ecommerce_add_product_cross_selling_products" role="tab-panel">
                            <div class="d-flex flex-column gap-7 gap-lg-10">
                                <!--begin::Related Products-->
                                <div class="card card-flush py-4">
                                    <!--begin::Card header-->
                                    {{--                                    <input type="hidden" id="product_id"value="{{ $product->id }}">--}}
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>Cross Selling Products</h2>
                                            <button type="button" id="saveCrossSellingProducts" class="btn btn-primary btn-sm">Save </button>                                        </div>
                                        <div class="card-toolbar">
                                            <input type="text" id="cross_selling_product_search" class="form-control form-control-sm"
                                                   placeholder="Search by SKU or Name">
                                        </div>

                                    </div>
                                    <!--end::Card header-->

                                @include('products::partials.cross_selling_products_table')

                                <!--end::Card body-->
                                </div>
                                <!--end::Related Products-->
                            </div>
                        </div>

                    </div>
                    <!--end::Tab content-->
                    <div class="d-flex justify-content-end">
                        <!--begin::Button-->
                        <a href="{{ $cancel_url }}" id="kt_ecommerce_add_product_cancel" class="btn btn-light me-5">Cancel</a>
                        <!--end::Button-->
                        <!--begin::Button kt_ecommerce_add_product_submit-->
                        <button type="submit" id="" class="btn btn-primary">
                            <span class="indicator-label">Update Changes</span>
                            <span class="indicator-progress">Please wait...
												<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                        <!--end::Button-->
                    </div>
                </div>
                <!--end::Main column-->
            </form>
            <!--end::Form-->
        </div>
        <!--end::Container-->
    </div>
@endsection
@section('custom-js-section')
    <!--end::Vendors Javascript-->
    <script src="{{ asset('build-base/ktmt/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('build-base/ktmt/plugins/custom/formrepeater/formrepeater.bundle.js') }}"></script>
    <!--begin::Custom Javascript(used for this page only)-->

    <script src="{{ asset('build-base/ktmt/js/custom/apps/ecommerce/catalog/save-product.js') }}"></script>
    <script src="{{ asset('build-base/ktmt/js/widgets.bundle.js') }}"></script>
    <script src="{{ asset('build-base/ktmt/js/custom/widgets.js') }}"></script>
    <script src="{{ asset('build-base/ktmt/js/custom/apps/chat/chat.js') }}"></script>
    <script src="{{ asset('build-base/ktmt/js/custom/utilities/modals/upgrade-plan.js') }}"></script>
    <script src="{{ asset('build-base/ktmt/js/custom/utilities/modals/create-app.js') }}"></script>
    <script src="{{ asset('build-base/ktmt/js/custom/utilities/modals/users-search.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('select').select2({
                placeholder: "Select Options",
                allowClear: true
            });
        });
    </script>

    <script>

        $(document).ready(function() {

            const editors = document.querySelectorAll('#description,#short_description,#meta_description,.attribute-link-textarea');
            editors.forEach(function (editor) {
                ClassicEditor
                    .create(editor)
                    .catch(error => {
                        console.error(error);
                    });
            });

            let specialPriceFrom = "{{ $product->special_price_from ?? '' }}";
            let specialPriceTo = "{{ $product->special_price_to ?? '' }}";

            // Check if the values exist, otherwise set them as null
            let startDate = specialPriceFrom ? moment(specialPriceFrom, "YYYY-MM-DD HH:mm:ss") : moment();
            let endDate = specialPriceTo ? moment(specialPriceTo, "YYYY-MM-DD HH:mm:ss") : moment().add(1, 'days');

            $("#special_price_date").daterangepicker({
                timePicker: true,
                startDate: startDate,
                endDate: endDate,
                locale: {
                    format: "M/D/Y"
                }
            });




            const categories = @json($categories);
            const selectedCategories = @json($productCategories);

            function buildTree(categories) {
                return categories.map(category => ({
                    id: category.id,
                    text: category.name,
                    children: buildTree(category.children || []),
                }));
            }

            $('#category-tree').jstree({
                'core': {
                    'data': buildTree(categories),
                    'check_callback': true,
                    'themes': {
                        'responsive': false
                    },
                },
                'plugins': ['dnd'],
            }).on('loaded.jstree', function () {
                $('#category-tree').jstree(true).select_node(selectedCategories);
            });

            $('#kt_ecommerce_update_product_form').on('submit', function (e) {
                let selectedCategoryIds = $('#category-tree').jstree('get_selected');
                $('#selected_category').val(selectedCategoryIds);
            });

            function populateProductAttributesSection() {
                let attributeSetId = $('#attribute_set_id').val();
                let productId = "{{ $product->id }}";

                if (attributeSetId.trim() !== '' && parseInt(attributeSetId) > 0) {
                    $.ajax({
                        url: "{{ route('products.get-product-attributes-content') }}",
                        type: "POST",
                        data: {
                            'attribute_set_id': attributeSetId,
                            'product_id': productId,
                            '_token': '{{ csrf_token() }}'
                        },
                        dataType: 'json'
                    }).done(function (data) {
                        console.log(data); // Debugging: Check if 'data.res' has valid HTML

                        if (data.res !== undefined && data.res.trim() !== '') {
                            $('.product_attributes_main_section').html(data.res);

                            // ✅ Reinitialize ClassicEditor for dynamically added textareas
                            document.querySelectorAll('.attribute-link-textarea').forEach(editor => {
                                ClassicEditor
                                    .create(editor)
                                    .catch(error => console.error(error));
                            });

                            // ✅ Reinitialize flatpickr for date fields
                            $('.attribute-link-date').flatpickr({
                                enableTime: false,
                                dateFormat: "d/m/Y",
                            });
                        } else {
                            console.warn("No attributes found for the selected attribute set.");
                        }
                    }).fail(function (jqXHR, textStatus, errorThrown) {
                        console.error("AJAX Error:", textStatus, errorThrown);
                    });
                }
            }
            populateProductAttributesSection();
            $('select#attribute_set_id').on('change', function (e) {
                populateProductAttributesSection();
            });

        });

    </script>


    <script>
        $(document).ready(function () {
            function initializeDataTable(tableId, ajaxUrl, searchField, checkboxClass, saveButton, saveRoute, productKey) {
                if ($.fn.DataTable.isDataTable(tableId)) {
                    $(tableId).DataTable().destroy(); // Destroy existing instance before reinitializing
                }

                $(tableId).DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: ajaxUrl,
                        type: "GET",
                        data: function (d) {
                            d.search = $(searchField).val();
                            d.product_id = $('#product_id').val();
                        },
                        error: function (xhr) {
                            Swal.fire({
                                title: 'Error loading data!',
                                text: xhr.responseText,
                                icon: 'error',
                            });
                        }
                    },
                    columns: [
                        { data: 'select', name: 'select', orderable: false, searchable: false },
                        { data: 'sku', name: 'sku' },
                        { data: 'name', name: 'name' }
                    ]
                });

                // Live Search - Refresh DataTable on input
                $(searchField).on('keyup', function () {
                    initializeDataTable(tableId, ajaxUrl, searchField, checkboxClass, saveButton, saveRoute, productKey);
                });

                $(saveButton).on('click', function () {
                    let selectedIds = $(checkboxClass + ':checked').map(function () {
                        return this.value;
                    }).get().join(','); // Convert array to comma-separated string

                    $.ajax({
                        url: saveRoute,
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            product_id: $('#product_id').val(),
                            [productKey]: selectedIds
                        },
                        success: function (response) {
                            Swal.fire({
                                title: 'Success',
                                text: response.message,
                                icon: 'success',
                            });
                        },
                        error: function (xhr) {
                            Swal.fire({
                                title: 'Error',
                                text: xhr.responseJSON.message,
                                icon: 'error',
                            });
                        }
                    });
                });
            }

            // Initialize both tables
            initializeDataTable(
                '#relatedProductsTable',
                "{{ route('products.related') }}",
                '#related_product_search',
                '.related_checkbox',
                '#saveRelatedProducts',
                "{{ route('products.saveRelated') }}",
                'related_product_ids'
            );

            initializeDataTable(
                '#crossSellingProductsTable',
                "{{ route('products.crossSelling') }}",
                '#cross_selling_product_search',
                '.cross_selling_checkbox',
                '#saveCrossSellingProducts',
                "{{ route('products.saveCrossSelling') }}",
                'cross_selling_product_ids'
            );

            var optionIndex = {{ isset($selectedOptions) ? count($selectedOptions) : 1 }};

            $('#add_option').click(function(){
                var newRow = `
                <div class="option-row mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <select class="form-select" data-control="select2"
                                    name="product_variant_options[`+optionIndex+`][option_id]"
                                    data-placeholder="Select an option">
                                <option value="">Select an Option</option>
                                @foreach($availableOptions as $option)
                <option value="{{ $option->id }}">{{ $option->name }}</option>
                                @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" name="product_variant_options[`+optionIndex+`][value]" class="form-control" placeholder="Enter value">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger remove-option">Remove</button>
                        </div>
                    </div>
                </div>
            `;
                $('#product_options_container').append(newRow);
                optionIndex++;
            });

            $(document).on('click', '.remove-option', function(){
                $(this).closest('.option-row').remove();
            });
        });
    </script>




@endsection
