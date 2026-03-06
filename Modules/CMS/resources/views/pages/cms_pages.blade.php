@extends('base::layouts.mt-main')

<style>
    .form-check-square .form-check-input {
        border-radius: 0;
    }
</style>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Inject CSRF token for AJAX requests --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

@section('content')

    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">
            <div class="card card-flush">
                <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                    <div class="card-title">
                        <div class="d-flex align-items-center position-relative my-1">
                            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                            <input type="text" class="form-control form-control-solid w-250px ps-12" placeholder="Search Pages" />
                        </div>
                    </div>
                    <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                        <div class="w-100 mw-150px">
                            <select class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="Status" data-kt-ecommerce-product-filter="status">
                                <option></option>
                                <option value="all">All</option>
                                <option value="published">Published</option>
                                <option value="unpublished">Unpublished</option>
                            </select>
                        </div>
                        {{-- Show Bulk Delete only if user has delete permission or is super admin --}}
                        @if(auth()->user()->is_super_admin || auth()->user()->can('delete_cms_pages'))
                            <button type="button" class="btn btn-danger" id="bulk-delete-btn">Delete Selected</button>
                        @endif
                        {{-- Show Add Page only if user has create permission or is super admin --}}
                        @if(auth()->user()->is_super_admin || auth()->user()->can('create_cms_pages'))
                            <a href="{{ route('cms.pages.create') }}" class="btn btn-primary">Add Page</a>
                        @endif
                    </div>
                </div>
                <div class="card-body pt-0">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_ecommerce_products_table">
                        <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            {{-- Show Checkbox column only if user has delete permission or is super admin --}}
                            @if(auth()->user()->is_super_admin || auth()->user()->can('delete_cms_pages'))
                                <th class="w-10px pe-2">
                                    <div class="form-check form-check-sm form-check-square form-check-custom form-check-solid me-3">
                                        <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_ecommerce_products_table .form-check-input" value="1" />
                                    </div>
                                </th>
                            @endif
                            <th class="min-w-100px">Title</th>
                            <th class="text-end min-w-100px">Slug</th>
                            <th class="text-end min-w-100px">Language</th>
                            <th class="text-end min-w-100px">Published</th>
                            {{-- Show Actions column only if user has edit or delete permission or is super admin --}}
                            @if(auth()->user()->is_super_admin || auth()->user()->can('edit_cms_pages') || auth()->user()->can('delete_cms_pages'))
                                <th class="text-end min-w-100px">Actions</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                        @foreach($pages as $page)
                            <tr>
                                {{-- Show Checkbox cell only if user has delete permission or is super admin --}}
                                @if(auth()->user()->is_super_admin || auth()->user()->can('delete_cms_pages'))
                                    <td>
                                        <div class="form-check form-check-sm form-check-square form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="{{ $page->id }}" />
                                        </div>
                                    </td>
                                @endif
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="ms-5">
                                            {{-- Link to edit only if user has edit permission or is super admin --}}
                                            @if(auth()->user()->is_super_admin || auth()->user()->can('edit_cms_pages'))
                                                <a href="{{ route('cms.pages.edit', $page->id) }}" class="text-gray-800 text-hover-primary fs-5 fw-bold">
                                                    {{ $page->title }}
                                                </a>
                                            @else
                                                <span class="text-gray-800 fs-5 fw-bold">{{ $page->title }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end pe-0">
                                    <span class="fw-bold">{{ $page->meta->slug ?? 'N/A' }}</span>
                                </td>
                                <td class="text-end pe-0">
                                    <span class="fw-bold">{{ $languages[$page->language] ?? 'N/A' }}</span>
                                </td>
                                <td class="text-end pe-0">
                                    @if($page->is_published)
                                        <span class="badge badge-light-success">Published</span>
                                    @else
                                        <span class="badge badge-light-danger">Unpublished</span>
                                    @endif
                                </td>
                                {{-- Show Actions cell only if user has edit or delete permission or is super admin --}}
                                @if(auth()->user()->is_super_admin || auth()->user()->can('edit_cms_pages') || auth()->user()->can('delete_cms_pages'))
                                    <td class="text-end">
                                        {{-- Show Edit button only if user has edit permission or is super admin --}}
                                        @if(auth()->user()->is_super_admin || auth()->user()->can('edit_cms_pages'))
                                            <a href="{{ route('cms.pages.edit', $page->id) }}" class="btn btn-sm btn-light btn-active-light-primary me-2">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        @endif
                                        {{-- Show Delete button only if user has delete permission or is super admin --}}
                                        @if(auth()->user()->is_super_admin || auth()->user()->can('delete_cms_pages'))
                                            <form action="{{ route('cms.pages.destroy', $page->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light btn-active-light-danger delete-banner" style="border:none; background:none;">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

<script>
    $(document).ready(function() {
        // Determine which columns are visible based on permissions
        const canDelete = {{ (auth()->user()->is_super_admin || auth()->user()->can('delete_cms_pages')) ? 'true' : 'false' }};
        const canEditOrDelete = {{ (auth()->user()->is_super_admin || auth()->user()->can('edit_cms_pages') || auth()->user()->can('delete_cms_pages')) ? 'true' : 'false' }};

        let columnDefs = [];
        let order = [];
        let statusColumnIndex;
        let actionsColumnIndex;
        let currentColumnIndex = 0; // Start counting columns from index 0

        // Column 0: Checkbox (optional)
        if (canDelete) {
            columnDefs.push({ orderable: false, targets: currentColumnIndex }); // Target 0 for checkbox
            currentColumnIndex++; // Increment index count
        }

        // Column 0 or 1: Title (this is the column we usually sort by initially)
        let titleColumnIndex = currentColumnIndex;
        order.push([titleColumnIndex, 'asc']); // Order by Title column
        currentColumnIndex++; // Increment for Title

        // Columns: Slug, Language
        currentColumnIndex += 2; // Increment for Slug and Language

        // Column 3 or 4: Published (Status)
        statusColumnIndex = currentColumnIndex;
        currentColumnIndex++; // Increment for Published

        // Column 4 or 5: Actions (optional)
        if (canEditOrDelete) {
            actionsColumnIndex = currentColumnIndex; // Assign the current index to Actions
            columnDefs.push({ orderable: false, targets: actionsColumnIndex }); // Target the correct index
            // currentColumnIndex++; // Increment if needed for further columns
        }

        // Initialize DataTable
        const table = $("#kt_ecommerce_products_table").DataTable({
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            order: order, // Use the dynamically calculated order array
            columnDefs: columnDefs, // Use the dynamically calculated columnDefs array
            searching: true
            // DataTables automatically detects columns from the <thead>.
            // The key is that the indices targeted in columnDefs must exist.
        });

        // --- Search Input Logic ---
        $('input[placeholder="Search Pages"]').on('keyup', function() {
            table.search($(this).val()).draw();
        });

        // --- Select2 Initialization ---
        $('.form-select[data-control="select2"]').select2({
            minimumResultsForSearch: Infinity // Assuming this is the status dropdown
        });

        // --- Status Filtering Logic ---
        // Ensure the correct status dropdown selector is used if needed
        $('select[data-kt-ecommerce-product-filter="status"]').on('change', function() {
            var status = $(this).val();
            var statusSearchTerm = '';

            // Use RegExp for exact match ("Published", not "Unpublished")
            // The ^ anchors the search to the start, $ anchors to the end
            if (status === 'published') {
                statusSearchTerm = '^Published$';
            } else if (status === 'unpublished') {
                statusSearchTerm = '^Unpublished$';
            }

            // Apply the search to the correctly calculated statusColumnIndex
            // The true, false parameters enable regex search and disable smart search respectively
            table.column(statusColumnIndex).search(statusSearchTerm, true, false).draw();
        });


        // --- Bulk Delete Logic (Only if canDelete is true) ---
        if (canDelete) {
            // Header checkbox logic
            $('[data-kt-check="true"]').on('change', function() {
                const isChecked = $(this).prop('checked');
                $('#kt_ecommerce_products_table .form-check-input').not(this).prop('checked', isChecked);
            });

            // Bulk delete button logic
            $('#bulk-delete-btn').on('click', function() {
                const selectedIds = [];
                // Select only the checkboxes in the table body that are checked
                $('#kt_ecommerce_products_table tbody .form-check-input:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    Swal.fire({
                        title: 'No Selection', // More specific title
                        text: 'Please select at least one page to delete.',
                        icon: 'warning', // Use warning for user action required
                        confirmButtonText: 'Ok'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Are you sure?',
                    text: `You are about to delete ${selectedIds.length} page(s). This action cannot be undone.`, // Dynamic text
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33', // Standard red for delete confirmation
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete them!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('cms.pages.bulk-delete') }}',
                            type: 'POST',
                            data: {
                                ids: selectedIds,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                Swal.fire({
                                    title: response.title || 'Deleted!',
                                    text: response.message || 'Selected pages have been deleted.',
                                    icon: response.icon || 'success',
                                    confirmButtonText: 'Ok'
                                }).then(() => {
                                    location.reload();s
                                });
                            },
                            error: function(xhr) {
                                const response = xhr.responseJSON || {};
                                Swal.fire({
                                    title: response.title || 'Error!',
                                    text: response.message || 'An error occurred while deleting pages.',
                                    icon: response.icon || 'error',
                                    confirmButtonText: 'Ok'
                                });
                            }
                        });
                    }
                });
            });
        }

        if (canDelete) {
            $(document).on('click', 'button.delete-banner', function(e) { // Target the button specifically
                e.preventDefault();
                const form = $(this).closest('form');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to delete this page. This action cannot be undone.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        }
    });
</script>

