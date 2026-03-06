@extends('base::layouts.mt-main')
@section('content')
    <div class="container">

        <h1>All Attributes</h1>
        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <!--begin:::Tabs-->
            <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-n2">
                <li class="nav-item">
                    <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab"
                       href="#kt_ecommerce_add_product_All_attributes">All Attributes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab"
                       href="#kt_ecommerce_add_product_Link">Link Variant</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-active-primary pb-4"
                       href="{{ route('product.variants.all', $parent_id) }}">View VAriants</a>
                </li>

            </ul>
            <!--end:::Tabs-->

            <!--begin::Tab content-->
            <div class="tab-content">
                <div class="tab-pane fade show active" id="kt_ecommerce_add_product_All_attributes" role="tabpanel">
                    <div class="d-flex flex-column gap-7 gap-lg-10">
                        <div class="card card-flush py-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>All Attributes</h2>

                                </div>
                                <div class="card-toolbar">
                                    <input type="text" id="attribute_search"
                                           class="form-control form-control-sm"
                                           placeholder="Search by Code or Name">

                                    <input type="hidden"  id="parentSku" value="{{ $parentProductSku }}">
                                </div>
                            </div>

                            <table class="table table-bordered" id="product_options_list_filter_table">
                                <thead>
                                <tr>
                                    <th><input type="checkbox" id="select-all"></th>
                                    <th>#</th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Active</th>
                                    <th>Created By</th>
                                    <th>Updated At</th>
                                    {{--                                    <th>Actions</th>--}}
                                </tr>
                                </thead>

                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade " id="kt_ecommerce_add_product_Link" role="tabpanel">
                    <div class="card card-flush py-4">
                        <div class="card-header">
                            <div class="card-title">
                                <h2>Product Options</h2>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div data-kt-ecommerce-catalog-add-product="auto-options">
                                <label class="form-label">Product Options</label>


                                <form id="variantForm" action="{{ route('products.storeVariants') }}" method="POST">
                                    @csrf
                                    <div id="linked-attributes-container" class="mt-3"></div>
                                <div id="variantContainer" class="mt-3"></div>
                                    <input type="hidden" name="parentId" id="parentId" value="{{ $parent_id }}">
                                    <button type="submit" class="btn btn-primary">Save Variants</button>
                                </form>
                            </div>
                        </div>
                    </div>


                </div>

            </div>
            <!--end::Tab content-->

        </div>

    </div>
@endsection

@section('custom-js-section')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   <script>

       $(document).ready(function () {
           var selectedAttributes = [];
           var id = $("#parentId").val();
           var dt;

           // Initialize DataTable
           function initDatatable() {
               dt = $("#product_options_list_filter_table").DataTable({
                   processing: true,
                   serverSide: true,
                   ajax: {
                       url: "/products/allAttributes/" + id,
                       type: "GET",
                       data: function (d) {
                           d.search = $('#attribute_search').val();
                       }
                   },
                   paging: true,
                   searching: false,
                   lengthChange: true,
                   pageLength: 10,
                   ordering: true,
                   info: true,
                   autoWidth: false,
                   columns: [
                       {
                           data: 'id', orderable: false, searchable: false, render: function (data, type, row) {
                               return `<input type="checkbox" class="attribute-checkbox" data-code="${row.code}" data-id="${row.id}">`;
                           }
                       },
                       {data: 'id', name: 'id'},
                       {data: 'code', name: 'code'},
                       {data: 'label', name: 'label'},
                       {
                           data: 'is_active', name: 'is_active', render: function (data) {
                               return data ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-danger">No</span>';
                           }
                       },
                       {data: 'created_by', name: 'created_by'},
                       {data: 'updated_at', name: 'updated_at'}
                   ]
               });

               dt.on('draw', function () {
                   restoreCheckedAttributes();
               });

               // Search filter
               $('#attribute_search').on('keyup', function () {
                   dt.ajax.reload();
               });

               // Select All Checkbox
               $('#select-all').on('click', function () {
                   $('.attribute-checkbox').prop('checked', this.checked).trigger('change');
               });
           }

           // Restore checked attributes after reload
           function restoreCheckedAttributes() {
               $('.attribute-checkbox').each(function () {
                   let code = $(this).data('code');
                   if (selectedAttributes.includes(code)) {
                       $(this).prop('checked', true);
                   }
               });
           }

           // Handle checkbox selection
           $(document).on('change', '.attribute-checkbox', function () {
               let attributeCode = $(this).data('code');

               if ($(this).is(':checked')) {
                   if (!selectedAttributes.includes(attributeCode)) {
                       selectedAttributes.push(attributeCode);
                   }
               } else {
                   selectedAttributes = selectedAttributes.filter(code => code !== attributeCode);
               }

               if (selectedAttributes.length > 0) {
                   fetchAttributes(selectedAttributes);
               } else {
                   $('#linked-attributes-container').empty();
                   $('#variantContainer').empty(); // Clear variants if no attributes
               }
           });

           // Fetch attribute options via AJAX
           function fetchAttributes(attributeCodes) {
               $.ajax({
                   url: "{{ route('products.getAttributeOptions') }}",
                   type: "GET",
                   data: {codes: attributeCodes},
                   success: function (response) {
                       updateLinkedAttributes(response.attributes);
                   },
                   error: function () {
                       $('#linked-attributes-container').empty();
                   }
               });
           }

           // Update linked attributes dynamically
           function updateLinkedAttributes(attributes) {
               let container = $('#linked-attributes-container');
               container.empty();

               attributes.forEach(attribute => {
                   let dropdown = `
            <div class="mb-3">
                <label class="form-label">${attribute.code}</label>
                <select name="attributes[${attribute.code}][]" class="form-select attribute-dropdown" multiple>
                    ${attribute.options.map(option => `
                        <option value="${option.english_value}">
                            ${option.english_value}
                        </option>
                    `).join('')}
                </select>
            </div>`;
                   container.append(dropdown);
               });

               // Attach event for variant generation (use event delegation)
               $(document).on('change', '.attribute-dropdown', generateVariants);

           }
           function generateVariants() {
               console.log("Generating variants..."); // Debugging step

               let selectedAttributes = {}; // Object to store selected attributes
               let variantContainer = $("#variantContainer");
               variantContainer.empty();

               // Loop through all selected attributes
               $(".attribute-dropdown").each(function () {
                   let attributeName = $(this).attr("name").match(/\[([^\]]+)\]/)[1]; // Extract attribute name
                   let values = $(this).val() || []; // Get selected values

                   if (values.length > 0) {
                       selectedAttributes[attributeName] = values;
                   }
               });

               console.log("Selected Attributes:", selectedAttributes); // Debugging

               // Convert selected attributes into an array of value arrays
               let attributeValues = Object.values(selectedAttributes);

               if (attributeValues.length === 0) {
                   console.log("No variants generated - Ensure at least one attribute is selected.");
                   return; // Ensure at least one attribute is selected
               }

               // Generate all possible combinations of attribute values
               let combinations = generateCombinations(attributeValues);

               // Render variant input fields for each combination
               combinations.forEach(combination => {
                   let variant = combination.join("-"); // Join attributes with "-"
                   let rowHtml = `
            <div class="variant-row d-flex gap-2 mb-2">
                <input type="text" name="variant_name[]" class="form-control" value="${variant}" readonly>
                <input type="text" name="sku[]" class="form-control" placeholder="SKU">
                <input type="number" name="price[]" class="form-control" placeholder="Price">
                <input type="number" name="qty[]" class="form-control" placeholder="Quantity">
            </div>`;
                   variantContainer.append(rowHtml);
               });

               console.log("Variants generated successfully.");
           }
           function generateCombinations(arrays) {
               var parentSku = $("#parentSku").val() || "SKU"; // Get parent SKU, default to "SKU"

               if (arrays.length === 0) return []; // Return empty array if no attributes

               let result = [];

               function recursiveCombine(remainingArrays, prefix) {
                   if (remainingArrays.length === 0) {
                       result.push(prefix); // Push final combination
                       return;
                   }

                   let firstArray = remainingArrays[0];
                   let restArrays = remainingArrays.slice(1);

                   firstArray.forEach(value => {
                       recursiveCombine(restArrays, [...prefix, value]); // Recursive call
                   });
               }

               recursiveCombine(arrays, []); // Start with empty prefix

               // Prepend parent SKU only once to each combination
               return result.map(combination => [parentSku, ...combination]);
           }
           // Initialize DataTable on page load
           KTUtil.onDOMContentLoaded(function () {
               initDatatable();
           });

       });

   </script>
@endsection
