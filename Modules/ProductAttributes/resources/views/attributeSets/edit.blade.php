@extends('base::layouts.mt-main')
@section('content')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" rel="stylesheet"/>

    <div class="container">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">

            <div class="card">

                <div class="card-body py-3">

                    <form action="{{ route('product.attribute.sets.update', $attributeSetData->id) }}" method="POST"
                          enctype="multipart/form-data">

                        <div class="card-header border-0 pt-5 d-flex justify-content-between align-items-center">
                            <h1 class="text-center mb-4">{{ __('Update Attribute Set') . '[Id: ' . $attributeSetData->id . ', Name: ' . $attributeSetData->name . ']' }}</h1>
                            <div class="d-flex gap-2">
                                <a href="{{ route('product.attribute.sets.index') }}" class="btn btn-sm btn-light">
                                    <i class="fas fa-arrow-left"></i> {{ __('Back to List') }}
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ __('Update') }}
                                </button>
                            </div>
                        </div>

                        @csrf

                        <div class="mb-5">
                            <label for="name" class="form-label">{{ __('Name') }}</label>
                            <label for="" class="form-label">{{ $attributeSetData->name }}</label>
                        </div>

                        <div class="mb-5">
                            <label for="label" class="form-label">{{ __('Label') }}</label>
                            <input type="text" name="label" id="label" class="form-control form-control-solid" required
                                   value="{{ $attributeSetData->label }}">
                        </div>

                        <div class="mb-5">
                            <label for="set_type" class="form-label">{{ __('Type') }}</label>
                            <select name="set_type" id="set_type" class="form-control form-control-solid" required>
                                @foreach($attributeSetTypes as $attributeTypeKey => $attributeTypeEl)
                                    <option value="{{ $attributeTypeKey }}"
                                            @if($attributeSetData->input_type == $attributeTypeKey) selected @endif>{{ $attributeTypeEl }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-5">
                            <label for="description" class="form-label">{{ __('Description') }}</label>
                            <textarea rows="6" cols="30" name="description"
                                      id="description">{{ $attributeSetData->description }}</textarea>
                        </div>

                        <div class="mb-5">
                            <label for="is_active" class="form-label">{{ __('Status') }}</label>
                            <select name="is_active" id="is_active" class="form-control form-control-solid">
                                @foreach($attributeSetStatuses as $attributeStatusKey => $attributeStatusEl)
                                    <option value="{{ $attributeStatusKey }}"
                                            @if($attributeSetData->is_active == $attributeStatusKey) selected @endif>{{ $attributeStatusEl }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-5">

                            <div class="row">
                                <label class="col col-3 col-form-label text-right">{{ __('Mapped Attributes') }}</label>
                                <div class="col col-6">
                                    <input type="hidden" name="items_selected_values" id="items_selected_values"
                                           value=""/>
                                    <input type="hidden" name="items_set_id" id="items_set_id"
                                           value="{{ $attributeSetData->id }}"/>
                                </div>
                                <div class="col col-3">
                                    <button type="button"
                                            class="btn btn-sm btn-primary waves-effect waves-light"
                                            data-bs-toggle="modal"
                                            data-attribute-set-name="{{ $attributeSetData->name }}"
                                            data-attribute-set-id="{{ $attributeSetData->id }}"
                                            data-attribute-set-action="new"
                                            data-bs-target="#linkAttributeModal">
                                        Link Attribute
                                    </button>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col col-12">

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-checkable text-center" id="attribute_set_map_table">

                                            <thead>
                                            <tr>
                                                <th>Id</th>
                                                <th>Code</th>
                                                <th>Label</th>
                                                <th>Input Type</th>
                                                <th>Value</th>
                                                <th>Description</th>
                                                <th>Sort Order</th>
                                                <th>Required</th>
                                                <th>Active</th>
                                                <th>Actions</th>
                                            </tr>
                                            </thead>

                                            <tbody>

                                            @foreach($attributeSetData->mappedAttributes as $attributeEl)
                                                <tr>
                                                    <td>{{ $attributeEl->id }}</td>
                                                    <td>{{ $attributeEl->code }}</td>
                                                    <td>{{ $attributeEl->label }}</td>
                                                    <td>{{ $attributeTypes[$attributeEl->input_type] }}</td>
                                                    <td>{!! $attributeEl->pivot->value !!}</td>
                                                    <td>{!! $attributeEl->pivot->description !!}</td>
                                                    <td>{{ $attributeEl->pivot->sort_order }}</td>
                                                    <td>{{ $attributeSetMapRequires[$attributeEl->pivot->is_required] }}</td>
                                                    <td>{{ $attributeSetMapStatuses[$attributeEl->pivot->is_active] }}</td>
                                                    <td>
                                                        <button type="button"
                                                                class="btn btn-sm btn-primary waves-effect waves-light"
                                                                data-bs-toggle="modal"
                                                                data-attribute-set-name="{{ $attributeSetData->name }}"
                                                                data-attribute-set-id="{{ $attributeSetData->id }}"
                                                                data-attribute-id="{{ $attributeEl->id }}"
                                                                data-attribute-code="{{ $attributeEl->code }}"
                                                                data-attribute-label="{{ $attributeEl->label }}"
                                                                data-attribute-set-action="edit"
                                                                data-bs-target="#linkAttributeEditModal">Edit
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach

                                            </tbody>

                                        </table>
                                    </div>

                                </div>
                            </div>

                        </div>

                    </form>

                </div>
            </div>

          <!-- Link Attribute Modal -->
<div class="modal fade" id="linkAttributeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fs-3 text-gray-800 fw-bolder" id="linkAttributeModalLabel"></h5>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <div class="row g-5 mb-5">
                    <input type="hidden" name="attribute_set_link_id" id="attribute_set_link_id" value="{{ $attributeSetData->id }}"/>

                    <div class="col-12 fv-row">
                        <label class="fs-6 fw-bold mb-2 required">{{ __('Attribute') }}</label>
                        <select name="attribute_link_id" id="attribute_link_id" 
                                class="form-select form-select-solid" 
                                data-control="select2"
                                data-placeholder="Select Attribute">
                            @foreach($activeAttributes as $attributeKey => $attributeEl)
                            <option value="{{ $attributeEl->id }}"
                                    data-attribute-type="{{ $attributeEl->input_type }}"
                                    data-attribute-value-needed="{{ in_array($attributeEl->input_type, array_keys($attributeValueTypes)) ? '1' : '0' }}">
                                {{ $attributeEl->label . ' (' . $attributeEl->code . ') - [' . $attributeTypes[$attributeEl->input_type]. ']' }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 fv-row" id="set_link_value_row" style="display: none;">
                        <label class="fs-6 fw-bold mb-2">{{ __('Value') }}</label>
                        <textarea name="set_link_value" id="set_link_value" 
                                class="form-control form-control-solid" 
                                rows="3"></textarea>
                    </div>

                    <div class="col-12 fv-row">
                        <label class="fs-6 fw-bold mb-2">{{ __('Description') }}</label>
                        <textarea name="set_link_description" id="set_link_description" 
                                class="form-control form-control-solid" 
                                rows="3"></textarea>
                    </div>

                    <div class="col-md-6 fv-row">
                        <label class="fs-6 fw-bold mb-2">{{ __('Sort Order') }}</label>
                        <input type="number" name="set_link_sort_order" id="set_link_sort_order" 
                             class="form-control form-control-solid" 
                             value="0" 
                             min="0"/>
                    </div>

                    <div class="col-md-6 fv-row">
                        <label class="fs-6 fw-bold mb-2">{{ __('Required') }}</label>
                        <select name="set_link_is_required" id="set_link_is_required" 
                                class="form-select form-select-solid">
                            @foreach($attributeSetMapRequires as $attributeStatusKey => $attributeStatusEl)
                            <option value="{{ $attributeStatusKey }}">{{ $attributeStatusEl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 fv-row">
                        <label class="fs-6 fw-bold mb-2">{{ __('Status') }}</label>
                        <select name="set_link_is_active" id="set_link_is_active" 
                                class="form-select form-select-solid">
                            @foreach($attributeSetMapStatuses as $attributeStatusKey => $attributeStatusEl)
                            <option value="{{ $attributeStatusKey }}">{{ $attributeStatusEl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer flex-center">
                <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">
                    {{ __('Close') }}
                </button>
                <button type="button" class="btn btn-primary link-attribute-modal-submit-btn">
                    <span class="indicator-label">{{ __('Link Attribute') }}</span>
                    <span class="indicator-progress">Please wait...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Attribute Modal -->
<div class="modal fade" id="linkAttributeEditModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fs-3 text-gray-800 fw-bolder" id="linkAttributeEditModalLabel"></h5>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <div class="row g-5 mb-5">
                    <div id="linkAttributeEditModalBody" class="col-12">
                        <!-- Dynamic content will be loaded here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer flex-center">
                <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">
                    {{ __('Close') }}
                </button>
                <button type="button" class="btn btn-primary link-attribute-modal-submit-btn">
                    <span class="indicator-label">{{ __('Update Attribute') }}</span>
                    <span class="indicator-progress">Please wait...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

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

        var KTDatatablesServerSide = function () {
            var dt;

            // Initialize DataTable
            var initDatatable = function () {
                dt = $("#attribute_set_map_table").DataTable({
                    responsive: true,
                    dom: `<'row'<'col-sm-12'tr>>
			        <'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 dataTables_pager'lp>>`,
                    lengthMenu: [10, 25, 50, 100],
                    pageLength: 10,
                    order: [[0, 'asc']],
                    columnDefs: [
                        {targets: [0], orderable: false}
                    ],
                    columns: [
                        {data: 'attributeId', className: 'text-wrap'},
                        {data: 'attributeCode', className: 'text-wrap'},
                        {data: 'attributeLabel', className: 'text-wrap'},
                        {data: 'attributeInputType', className: 'text-wrap'},
                        {data: 'attributeValue', className: 'text-wrap'},
                        {data: 'attributeDesc', className: 'text-wrap'},
                        {data: 'attributeSortOrder', className: 'text-wrap'},
                        {data: 'isRequired', className: 'text-wrap'},
                        {data: 'isActive', className: 'text-wrap'},
                        {data: 'actions', className: 'text-wrap', responsivePriority: -1},
                    ],
                });

                $('#linkAttributeModal').on('shown.bs.modal', function (event) {
                    let button = $(event.relatedTarget);
                    let id = button.data('attribute-set-id');
                    let attributeSetName = button.data('attribute-set-name');
                    let attributeSetAction = button.data('attribute-set-action');
                    let attributeId = $('#attribute_link_id').val();
                    let attributeValueCheck = $('#attribute_link_id option:selected').data('attribute-value-needed');
                    if (parseInt(attributeValueCheck) === 1) {
                        $('#set_link_value_row').show();
                    } else {
                        $('#set_link_value_row').hide();
                    }
                });

                $('#attribute_link_id').on('change', function (ev) {
                    let attributeValueCheck = $(this).children('option:selected').data('attribute-value-needed');
                    if (parseInt(attributeValueCheck) === 1) {
                        $('#set_link_value_row').show();
                    } else {
                        $('#set_link_value_row').hide();
                    }
                });

                $('#linkAttributeEditModal').on('shown.bs.modal', function (event) {
                    $('#linkAttributeEditModal').find('#linkAttributeEditModalBody').html('<h4 class="text-center mt-5 mb-5">Please wait...</h4>')
                    let button = $(event.relatedTarget);
                    let attributeSetId = button.data('attribute-set-id');
                    let attributeSetName = button.data('attribute-set-name');
                    let attributeId = button.data('attribute-id');
                    let attributeCode = button.data('attribute-code');
                    let attributeLabel = button.data('attribute-label');
                    let attributeSetAction = button.data('attribute-set-action');
                    $('#linkAttributeEditModalLabel').html('Attribute Set Edit' + ' - ' + attributeSetName + ' - ' + attributeLabel);
                    $.ajax({
                        url: "{{ route('product.attribute.sets.link.attribute.view') }}",
                        data: {
                            'attribute_set_id': attributeSetId,
                            'attribute_id': attributeId,
                            'action': attributeSetAction
                        },
                        type: "GET",
                        dataType: 'json'
                    }).done(function (data) {
                        let detailHtml = (data.res !== undefined) ? data.res : '';
                        $('#linkAttributeEditModal').find('#linkAttributeEditModalBody').html(detailHtml);
                    });
                });

                $('.link-attribute-modal-submit-btn').on('click', function (ev) {
                    let attributeSetId = $('#attribute_set_link_id').val();
                    let attributeId = $('#attribute_link_id').val();
                    let attributeValue = $('#set_link_value').val();
                    let linkDesc = $('#set_link_description').val();
                    let linkSortOrder = $('#set_link_sort_order').val();
                    let linkIsRequired = $('#set_link_is_required').val();
                    let linkIsActive = $('#set_link_is_active').val();
                    let postData = {
                        attribute_id: attributeId,
                        attribute_set_id: attributeSetId,
                        attribute_value: attributeValue,
                        attribute_desc: linkDesc,
                        attribute_sort_order: linkSortOrder,
                        attribute_is_required: linkIsRequired,
                        attribute_is_active: linkIsActive,
                        _token: "{{ csrf_token() }}"
                    };
                    $.ajax({
                        url: "{{ route('product.attribute.sets.link.attribute.process') }}",
                        type: 'POST',
                        data: postData,
                        success: function (response) {
                            Swal.fire({
                                text: response.message,
                                icon: "success",
                                buttonsStyling: false,
                                confirmButtonText: "Ok, got it!",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });
                            location.reload();
                        },
                        error: function (xhr, status, error) {
                            console.log('Link Attribute failed:', error);
                            Swal.fire({
                                text: "Failed to link the attribute. Please try again.",
                                icon: "error",
                            });
                        }
                    });
                });

                $(document).on('click', '.edit-attribute-modal-submit-btn', function (ev) {
                    let attributeSetId = $('#attribute_set_edit_id').val();
                    let attributeId = $('#attribute_edit_id').val();
                    let attributeValue = $('#set_edit_value').val();
                    let linkDesc = $('#set_edit_description').val();
                    let linkSortOrder = $('#set_edit_sort_order').val();
                    let linkIsRequired = $('#set_edit_is_required').val();
                    let linkIsActive = $('#set_edit_is_active').val();
                    let postData = {
                        attribute_id: attributeId,
                        attribute_set_id: attributeSetId,
                        attribute_value: attributeValue,
                        attribute_desc: linkDesc,
                        attribute_sort_order: linkSortOrder,
                        attribute_is_required: linkIsRequired,
                        attribute_is_active: linkIsActive,
                        _token: "{{ csrf_token() }}"
                    };
                    $.ajax({
                        url: "{{ route('product.attribute.sets.link.attribute.process') }}",
                        type: 'POST',
                        data: postData,
                        success: function (response) {
                            Swal.fire({
                                text: response.message,
                                icon: "success",
                                buttonsStyling: false,
                                confirmButtonText: "Ok, got it!",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });
                            location.reload();
                        },
                        error: function (xhr, status, error) {
                            console.log('Link Attribute failed:', error);
                            Swal.fire({
                                text: "Failed to link the attribute. Please try again.",
                                icon: "error",
                            });
                        }
                    });
                });

            };

            let showAlertMessage = function (message, type = 'info') {
                if (message.trim() !== '') {
                    let divClass = 'alert-dark alert-light-dark';
                    let iconClass = 'flaticon-information';
                    if (type === 'success') {
                        divClass = 'alert-success alert-light-success';
                        iconClass = 'flaticon2-check-mark';
                    } else if (type === 'error') {
                        divClass = 'alert-danger alert-light-danger';
                        iconClass = 'flaticon2-warning';
                    }
                    $("div.custom_alert_trigger_messages_area")
                        .html('<div class="alert alert-custom ' + divClass + ' fade show" role="alert">' +
                            '<div class="alert-icon"><i class="' + iconClass + '"></i></div>' +
                            '<div class="alert-text">' + message + '</div>' +
                            '<div class="alert-close">' +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                            '<span aria-hidden="true"><i class="ki ki-close"></i></span>' +
                            '</button>' +
                            '</div>' +
                            '</div>');
                }
            };

            return {
                init: function () {
                    initDatatable();
                }
            };
        }();

        KTUtil.onDOMContentLoaded(function () {
            KTDatatablesServerSide.init();
        });

    </script>

@endsection
