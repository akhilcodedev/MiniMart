@extends('base::layouts.mt-main')
@section('content')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" rel="stylesheet"/>

    <div class="container">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Form-->
            <form id="kt_ecommerce_add_product_form" class="form d-flex flex-column flex-lg-row"
                  action="{{ route('store.variant.product')}}" method="POST" enctype="multipart/form-data">
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
                                    <option value="{{ $type->id }}">{{ ucwords($type->name) }}</option>
                                @endforeach

                            </select>

                            <div class="d-none mt-10">
                                <label for="kt_ecommerce_add_product_status_datepicker" class="form-label">Select publishing date and time</label>
                                <input class="form-control" id="kt_ecommerce_add_product_status_datepicker" placeholder="Pick date & time" />
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
                                <option value="active" selected="selected">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <!--end::Select2-->
                            <!--begin::Description-->
                            <div class="text-muted fs-7">Set the product status.</div>
                            <!--end::Description-->
                            <!--begin::Datepicker-->
                            <div class="d-none mt-10">
                                <label for="kt_ecommerce_add_product_status_datepicker" class="form-label">Select publishing date and time</label>
                                <input class="form-control" id="kt_ecommerce_add_product_status_datepicker" placeholder="Pick date & time" />
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
                                <option value="1" selected="selected">In Stock</option>
                                <option value="0">Out Of Stock</option>
                            </select>
                            <!--end::Select2-->
                            <!--begin::Description-->
                            <div class="text-muted fs-7">Set the product status.</div>
                            <!--end::Description-->
                            <!--begin::Datepicker-->
                            <div class="d-none mt-10">
                                <label for="kt_ecommerce_add_product_status_datepicker" class="form-label">Select publishing date and time</label>
                                <input class="form-control" id="kt_ecommerce_add_product_status_datepicker" placeholder="Pick date & time" />
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



                            <a href="{{ route('categories.index') }}" class="btn btn-light-primary btn-sm mb-10">
                                <i class="ki-outline ki-plus fs-2"></i>Create new category</a>

                            <label class="form-label">Languages</label>
                            <!--end::Label-->
                            <!--begin::Select2 for languages-->
                            <select class="form-select mb-2" data-control="select2"
                                    name = "language_id" data-placeholder="Select languages" data-allow-clear="true" multiple="multiple">
                                <option></option>
                                @foreach($languages as $language)
                                    <option value="{{ $language->id }}">{{ $language->name }}</option>
                                @endforeach
                            </select>
                            <!--end::Select2 for languages-->
                            <!--begin::Description-->
                            <div class="text-muted fs-7 mb-7">Select languages for the product.</div>

                            <label class="form-label d-block">Tags</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input id="kt_ecommerce_add_product_tags" name="kt_ecommerce_add_product_tags" class="form-control mb-2" value="" />
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
                    <div class="d-flex justify-content-end">
                        <!--begin::Button-->
                        <a href="{{ $cancel_url }}" id="kt_ecommerce_add_product_cancel" class="btn btn-light me-5">Cancel</a>
                        <!--end::Button-->
                        <!--begin::Button kt_ecommerce_add_product_submit-->
                        <button type="submit" id="" class="btn btn-primary">
                            <span class="indicator-label">Save Changes</span>
                            <span class="indicator-progress">Please wait...
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                        <!--end::Button-->
                    </div>
                    <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-n2">
                        <!--begin:::Tab item-->
                        <li class="nav-item">
                            <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab" href="#kt_ecommerce_add_product_general">General</a>
                        </li>
                        <!--end:::Tab item-->
                        <!--begin:::Tab item-->
                        <li class="nav-item">
                            <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#kt_ecommerce_add_product_advanced">Advanced</a>
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
                                            <input type="text" name="product_name" class="form-control mb-2" placeholder="Product name" value="" />
                                            <!--end::Input-->
                                            <!--begin::Description-->
                                            <div class="text-muted fs-7">A product name is required and recommended to be unique.</div>
                                            <!--end::Description-->
                                        </div>
                                        <div class="mb-10 fv-row">
                                            <!--begin::Label-->
                                            <label class="required form-label">URL Key</label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="text" name="url_key" class="form-control mb-2" placeholder="URL Key" value="" />
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

                                            <textarea id="short_description" name="short_description"rows="10"></textarea>
                                            <!--end::Editor-->
                                            <!--begin::Description-->
                                            <div class="text-muted fs-7">Set a short description to the product for better visibility.</div>
                                            <!--end::Description-->
                                        </div>
                                        <!--end::Input group-->

                                        <!--begin::Input group-->
                                        <div>
                                            <!--begin::Label-->
                                            <label class="form-label">Description</label>
                                            <!--end::Label-->
                                            <!--begin::Editor-->

                                            <textarea id="description" name="description" style="background: black"></textarea>

                                            <!--end::Editor-->
                                            <!--begin::Description-->
                                            <div class="text-muted fs-7">Set a description to the product for better visibility.</div>
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
{{--                                        <input class="form-control" type="file" name="product_image[]" id="formFileMultiple" multiple>--}}
                                        <div class="fv-row mb-2">
                                            <!--begin::Dropzone-->
{{--                                            <div class="dropzone" id="kt_ecommerce_add_product_media" name="product_image[]">--}}

{{--                                                <!--begin::Message-->--}}
{{--                                                <div class="dz-message needsclick">--}}
{{--                                                    <!--begin::Icon-->--}}
{{--                                                    <i class="ki-outline ki-file-up text-primary fs-3x"></i>--}}
{{--                                                    <!--end::Icon-->--}}
{{--                                                    <!--begin::Info-->--}}
{{--                                                    <div class="ms-4">--}}
{{--                                                        <h3 class="fs-5 fw-bold text-gray-900 mb-1">Drop files here or click to upload.</h3>--}}
{{--                                                        <span class="fs-7 fw-semibold text-gray-500">Upload up to 10 files</span>--}}
{{--                                                    </div>--}}
{{--                                                    <!--end::Info-->--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
                                            <input type="file" class="form-control mb-2" name="product_image[]" id="kt_ecommerce_add_product_image" multiple>

{{--                                            <div class="custom-file">--}}
{{--                                                <input type="file" class="custom-file-input" name="product_image[]"id="product_image"multiple>--}}
{{--                                                <label class="custom-file-label" for="customFile">Choose file</label>--}}
{{--                                            </div>--}}
                                            <!--end::Dropzone-->
                                        </div>
                                        <!--end::Input group-->
                                        <!--begin::Description-->
                                        <div class="text-muted fs-7">Set the product media gallery.</div>
                                        <!--end::Description-->
                                    </div>
                                    <!--end::Card header-->
                                </div>
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

                                            <label class="required form-label">Base Price</label>

                                            <input type="text" name="price" class="form-control mb-2" placeholder="Product price" value="" />

                                            <div class="text-muted fs-7">Set the product price.</div>

                                            <label class="required form-label">Special Price</label>

                                            <input type="text" name="special_price" class="form-control mb-2" placeholder="Special price" value="" />

                                            <div class="text-muted fs-7">Set the product Special price.</div>

                                            <label class="form-label">Special price valid up to</label>
                                            <input class="form-control" placeholder="Pick date rage" name="special_price_date" id="special_price"value=""/>

                                        </div>
                                        <!--end::Input group-->
                                        <!--begin::Input group-->
{{--                                        <div class="fv-row mb-10">--}}
{{--                                            <!--begin::Label-->--}}
{{--                                            <label class="fs-6 fw-semibold mb-2">Discount Type--}}
{{--                                                <span class="ms-1" data-bs-toggle="tooltip" title="Select a discount type that will be applied to this product">--}}
{{--																	<i class="ki-outline ki-information-5 text-gray-500 fs-6"></i>--}}
{{--																</span></label>--}}
{{--                                            <!--End::Label-->--}}
{{--                                            <!--begin::Row-->--}}
{{--                                            <div class="row row-cols-1 row-cols-md-3 row-cols-lg-1 row-cols-xl-3 g-9" data-kt-buttons="true" data-kt-buttons-target="[data-kt-button='true']">--}}
{{--                                                <!--begin::Col-->--}}
{{--                                                <div class="col">--}}
{{--                                                    <!--begin::Option-->--}}
{{--                                                    <label class="btn btn-outline btn-outline-dashed btn-active-light-primary active d-flex text-start p-6" data-kt-button="true">--}}
{{--                                                        <!--begin::Radio-->--}}
{{--                                                        <span class="form-check form-check-custom form-check-solid form-check-sm align-items-start mt-1">--}}
{{--																				<input class="form-check-input" type="radio" name="discount_option" value="1" checked="checked" />--}}
{{--																			</span>--}}
{{--                                                        <!--end::Radio-->--}}
{{--                                                        <!--begin::Info-->--}}
{{--                                                        <span class="ms-5">--}}
{{--																				<span class="fs-4 fw-bold text-gray-800 d-block">No Discount</span>--}}
{{--																			</span>--}}
{{--                                                        <!--end::Info-->--}}
{{--                                                    </label>--}}
{{--                                                    <!--end::Option-->--}}
{{--                                                </div>--}}
{{--                                                <!--end::Col-->--}}
{{--                                                <!--begin::Col-->--}}
{{--                                                <div class="col">--}}
{{--                                                    <!--begin::Option-->--}}
{{--                                                    <label class="btn btn-outline btn-outline-dashed btn-active-light-primary d-flex text-start p-6" data-kt-button="true">--}}
{{--                                                        <!--begin::Radio-->--}}
{{--                                                        <span class="form-check form-check-custom form-check-solid form-check-sm align-items-start mt-1">--}}
{{--																				<input class="form-check-input" type="radio" name="discount_option" value="2" />--}}
{{--																			</span>--}}
{{--                                                        <!--end::Radio-->--}}
{{--                                                        <!--begin::Info-->--}}
{{--                                                        <span class="ms-5">--}}
{{--																				<span class="fs-4 fw-bold text-gray-800 d-block">Percentage %</span>--}}
{{--																			</span>--}}
{{--                                                        <!--end::Info-->--}}
{{--                                                    </label>--}}
{{--                                                    <!--end::Option-->--}}
{{--                                                </div>--}}
{{--                                                <!--end::Col-->--}}
{{--                                                <!--begin::Col-->--}}
{{--                                                <div class="col">--}}
{{--                                                    <!--begin::Option-->--}}
{{--                                                    <label class="btn btn-outline btn-outline-dashed btn-active-light-primary d-flex text-start p-6" data-kt-button="true">--}}
{{--                                                        <!--begin::Radio-->--}}
{{--                                                        <span class="form-check form-check-custom form-check-solid form-check-sm align-items-start mt-1">--}}
{{--																				<input class="form-check-input" type="radio" name="discount_option" value="3" />--}}
{{--																			</span>--}}
{{--                                                        <!--end::Radio-->--}}
{{--                                                        <!--begin::Info-->--}}
{{--                                                        <span class="ms-5">--}}
{{--																				<span class="fs-4 fw-bold text-gray-800 d-block">Fixed Price</span>--}}
{{--																			</span>--}}
{{--                                                        <!--end::Info-->--}}
{{--                                                    </label>--}}
{{--                                                    <!--end::Option-->--}}
{{--                                                </div>--}}
{{--                                                <!--end::Col-->--}}
{{--                                            </div>--}}
{{--                                            <!--end::Row-->--}}
{{--                                        </div>--}}
                                        <!--end::Input group-->
                                        <!--begin::Input group-->
                                        <div class="d-none mb-10 fv-row" id="kt_ecommerce_add_product_discount_percentage">
                                            <!--begin::Label-->
                                            <label class="form-label">Set Discount Percentage</label>
                                            <!--end::Label-->
                                            <!--begin::Slider-->
                                            <div class="d-flex flex-column text-center mb-5">
                                                <div class="d-flex align-items-start justify-content-center mb-7">
                                                    <span class="fw-bold fs-3x" id="kt_ecommerce_add_product_discount_label">0</span>
                                                    <span class="fw-bold fs-4 mt-1 ms-2">%</span>
                                                </div>
                                                <div id="kt_ecommerce_add_product_discount_slider" class="noUi-sm" style="background: black"></div>
                                            </div>
                                            <!--end::Slider-->
                                            <!--begin::Description-->
                                            <div class="text-muted fs-7">Set a percentage discount to be applied on this product.</div>
                                            <!--end::Description-->
                                        </div>
                                        <!--end::Input group-->
                                        <!--begin::Input group-->
{{--                                        <div class="d-none mb-10 fv-row" id="kt_ecommerce_add_product_discount_fixed">--}}
{{--                                            <!--begin::Label-->--}}
{{--                                            <label class="form-label">Fixed Discounted Price</label>--}}
{{--                                            <!--end::Label-->--}}
{{--                                            <!--begin::Input-->--}}
{{--                                            <input type="text" name="dicsounted_price" class="form-control mb-2" placeholder="Discounted price" />--}}
{{--                                            <!--end::Input-->--}}
{{--                                            <!--begin::Description-->--}}
{{--                                            <div class="text-muted fs-7">Set the discounted product price. The product will be reduced at the determined fixed price</div>--}}
{{--                                            <!--end::Description-->--}}
{{--                                        </div>--}}
                                        <!--end::Input group-->
                                        <!--begin::Tax-->
{{--                                        <div class="d-flex flex-wrap gap-5">--}}
{{--                                            <!--begin::Input group-->--}}
{{--                                            <div class="fv-row w-100 flex-md-root">--}}
{{--                                                <!--begin::Label-->--}}
{{--                                                <label class="required form-label">Tax Class</label>--}}
{{--                                                <!--end::Label-->--}}
{{--                                                <!--begin::Select2-->--}}
{{--                                                <select class="form-select mb-2" name="tax" data-control="select2" data-hide-search="true" data-placeholder="Select an option">--}}
{{--                                                    <option></option>--}}
{{--                                                    <option value="0">Tax Free</option>--}}
{{--                                                    <option value="1">Taxable Goods</option>--}}
{{--                                                    <option value="2">Downloadable Product</option>--}}
{{--                                                </select>--}}
{{--                                                <!--end::Select2-->--}}
{{--                                                <!--begin::Description-->--}}
{{--                                                <div class="text-muted fs-7">Set the product tax class.</div>--}}
{{--                                                <!--end::Description-->--}}
{{--                                            </div>--}}
{{--                                            <!--end::Input group-->--}}
{{--                                            <!--begin::Input group-->--}}
{{--                                            <div class="fv-row w-100 flex-md-root">--}}
{{--                                                <!--begin::Label-->--}}
{{--                                                <label class="form-label">VAT Amount (%)</label>--}}
{{--                                                <!--end::Label-->--}}
{{--                                                <!--begin::Input-->--}}
{{--                                                <input type="text" class="form-control mb-2" value="" />--}}
{{--                                                <!--end::Input-->--}}
{{--                                                <!--begin::Description-->--}}
{{--                                                <div class="text-muted fs-7">Set the product VAT about.</div>--}}
{{--                                                <!--end::Description-->--}}
{{--                                            </div>--}}
{{--                                            <!--end::Input group-->--}}
{{--                                        </div>--}}
                                        <!--end:Tax-->
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
                                            <input type="text" name="sku" class="form-control mb-2" placeholder="SKU Number" value="" />
                                            <!--end::Input-->
                                            <!--begin::Description-->
                                            <div class="text-muted fs-7">Enter the product SKU.</div>
                                            <!--end::Description-->
                                        </div>
                                        <!--end::Input group-->
                                        <!--begin::Input group-->
{{--                                        <div class="mb-10 fv-row">--}}
{{--                                            <!--begin::Label-->--}}
{{--                                            <label class="required form-label">Barcode</label>--}}
{{--                                            <!--end::Label-->--}}
{{--                                            <!--begin::Input-->--}}
{{--                                            <input type="text" name="barcode" class="form-control mb-2" placeholder="Barcode Number" value="" />--}}
{{--                                            <!--end::Input-->--}}
{{--                                            <!--begin::Description-->--}}
{{--                                            <div class="text-muted fs-7">Enter the product barcode number.</div>--}}
{{--                                            <!--end::Description-->--}}
{{--                                        </div>--}}
                                        <!--end::Input group-->
                                        <!--begin::Input group-->
                                        <div class="mb-10 fv-row">
                                            <!--begin::Label-->
                                            <label class="required form-label">Quantity</label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <div class="d-flex gap-3">
                                                <input type="number" name="quantity" class="form-control mb-2" placeholder="Product Quantity" value="" />
{{--                                                <input type="number" name="store" class="form-control mb-2" placeholder="In Store" />--}}
                                            </div>
                                            <!--end::Input-->
                                            <!--begin::Description-->
                                            <div class="text-muted fs-7">Enter the product quantity.</div>
                                            <!--end::Description-->
                                        </div>
                                        <!--end::Input group-->
                                        <!--begin::Input group-->
{{--                                        <div class="fv-row">--}}
{{--                                            <!--begin::Label-->--}}
{{--                                            <label class="form-label">Allow Backorders</label>--}}
{{--                                            <!--end::Label-->--}}
{{--                                            <!--begin::Input-->--}}
{{--                                            <div class="form-check form-check-custom form-check-solid mb-2">--}}
{{--                                                <input class="form-check-input" type="checkbox" value="" />--}}
{{--                                                <label class="form-check-label">Yes</label>--}}
{{--                                            </div>--}}
{{--                                            <!--end::Input-->--}}
{{--                                            <!--begin::Description-->--}}
{{--                                            <div class="text-muted fs-7">Allow customers to purchase products that are out of stock.</div>--}}
{{--                                            <!--end::Description-->--}}
{{--                                        </div>--}}
                                        <!--end::Input group-->
                                    </div>
                                    <!--end::Card header-->
                                </div>
                                <!--end::Inventory-->
                                <!--begin::Variations-->
                                <div class="card card-flush py-4">
                                    <!--begin::Card header-->
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>Variations</h2>
                                        </div>
                                    </div>

                                </div>

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
                                            <input type="text" class="form-control mb-2" name="meta_title" placeholder="Meta tag name" />
                                            <!--end::Input-->
                                            <!--begin::Description-->
                                            <div class="text-muted fs-7">Set a meta tag title. Recommended to be simple and precise keywords.</div>
                                            <!--end::Description-->
                                        </div>
                                        <!--end::Input group-->
                                        <!--begin::Input group-->
                                        <div class="mb-10">
                                            <!--begin::Label-->
                                            <label class="form-label">Meta Tag Description</label>
                                            <!--end::Label-->
                                            <!--begin::Editor-->
{{--                                            <div id="kt_ecommerce_add_product_meta_description" name="kt_ecommerce_add_product_meta_description" class="min-h-100px mb-2"></div>--}}
                                            <!--end::Editor-->
                                            <textarea id="meta_description" name="meta_description"></textarea>
                                            <!--begin::Description-->
                                            <div class="text-muted fs-7">Set a meta tag description to the product for increased SEO ranking.</div>
                                            <!--end::Description-->
                                        </div>
                                        <!--end::Input group-->
                                        <!--begin::Input group-->
                                        <div>
                                            <!--begin::Label-->
                                            <label class="form-label">Meta Tag Keywords</label>
                                            <!--end::Label-->
                                            <!--begin::Editor-->
                                            <input id="kt_ecommerce_add_product_meta_keywords" name="meta_keywords" class="form-control mb-2" />
                                            <!--end::Editor-->
                                            <!--begin::Description-->
                                            <div class="text-muted fs-7">Set a list of keywords that the product is related to. Separate the keywords by adding a comma
                                                <code>,</code>between each keyword.</div>
                                            <!--end::Description-->
                                        </div>
                                        <div>
                                            <!--begin::Label-->
                                            <label class="form-label">Product Meta Image</label>
                                            <!--end::Label-->
                                            <!--begin::Editor-->
                                            <input type="file" class="form-control mb-2" name="meta_image" id="kt_ecommerce_add_product_meta_keywords">
                                            <!--end::Editor-->


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
                                                <input type="number" name="out_of_stock_threshold" id="out_of_stock_threshold" class="form-control mb-2" placeholder="" value="0" />
                                            </div>
                                        </div>

                                        <div class="mb-5 fv-row">
                                            <label class="form-label" for="min_qty_allowed_in_shopping_cart">Minimum Qty Allowed in Shopping Cart</label>
                                            <div class="d-flex gap-3">
                                                <input type="number" name="min_qty_allowed_in_shopping_cart" id="min_qty_allowed_in_shopping_cart" class="form-control mb-2" placeholder="" value="" />
                                            </div>
                                        </div>

                                        <div class="mb-5 fv-row">
                                            <label class="form-label" for="max_qty_allowed_in_shopping_cart">Maximum Qty Allowed in Shopping Cart</label>
                                            <div class="d-flex gap-3">
                                                <input type="number" name="max_qty_allowed_in_shopping_cart" id="max_qty_allowed_in_shopping_cart" class="form-control mb-2" placeholder="" value="" />
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
                                                    <option value="0" selected>No</option>
                                                    <option value="1">Yes</option>
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
                                                    <option value="0" selected >No Backorders</option>
                                                    <option value="1">Allow Qty Below 0</option>
                                                    <option value="2">Allow Qty Below 0 and Notify Customer</option>
                                                    <option value="3">Allow Pre-Order</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Meta options-->
                            </div>
                        </div>
                        <!--end::Tab pane-->
                    </div>
                    <!--end::Tab content-->

                </div>
                <!--end::Main column-->
                    <input type="hidden" id="parent_id" name="parent_id" value="{{ $parent_product_id }}">
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
    <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>

    <script>

        const editors = document.querySelectorAll('#description,#short_description,#meta_description,#language_id');

        editors.forEach(function (editor) {
            ClassicEditor
                .create(editor)
                .catch(error => {
                    console.error(error);
                });
        });
        $("#special_price").daterangepicker();
        $(document).ready(function () {
            const categories = @json($categories);

            function buildTree(categories) {
                return categories.map(category => ({
                    id: category.id,
                    text: category.name,
                    children: buildTree(category.children || [])
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
            });

            $('#kt_ecommerce_add_product_form').on('submit', function (e) {
                let selectedCategoryIds = $('#category-tree').jstree('get_selected');
                if (selectedCategoryIds.length === 0) {
                    Swal.fire({
                        text: "Please select at least one category!.",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                    e.preventDefault();
                    return;
                }
                $('#selected_category').val(selectedCategoryIds);
            });

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
