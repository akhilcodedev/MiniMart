@extends('base::layouts.mt-main')

@section('content')
    {{-- CKEditor Script (Load regardless of permissions if used elsewhere, or conditionally load it if only for this form) --}}
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>

    {{-- Create Template Form Section --}}
    @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('create_email_templates')))
        <div class="card mb-7">
            <div class="card-header">
                <h3 class="card-title">{{ __('Create New Email Template') }}</h3>
            </div>
            <div class="card-body">
                {{-- Ensure the route 'email.templates.store' exists and is correct --}}
                <form class="row needs-validation" method="POST" action="{{ route('email.templates.store') }}" novalidate>
                    @csrf
                    <div class="col-md-4 mb-7">
                        <label for="slug" class="form-label fw-semibold mb-2 required">{{ __('Slug') }}</label>
                        <input type="text" class="form-control form-control-solid" id="slug" name="slug" value="{{ old('slug') }}" required>
                        <div class="invalid-feedback">{{ __('Please provide a valid slug.') }}</div>
                    </div>

                    <div class="col-md-4 mb-7">
                        <label for="tags" class="form-label fw-semibold mb-2 required">{{ __('Tags') }}</label>
                        <input type="text" class="form-control form-control-solid" id="tags" name="tags" value="{{ old('tags') }}" required placeholder=" {name}, {title}, {description}">
                        <div class="form-text text-muted small">{{ __('Comma-separated placeholders like {name}, {order_id} etc.') }}</div>
                        <div class="invalid-feedback">{{ __('Please provide placeholder Tags (e.g., {name}).') }}</div>
                    </div>

                    <div class="col-md-4 mb-7">
                        <label for="label" class="form-label fw-semibold mb-2">{{ __('Label') }}</label>
                        <input type="text" class="form-control form-control-solid" id="label" name="label" value="{{ old('label') }}"
                               placeholder="Customer Register Notification">
                        <div class="form-text text-muted small">{{ __('A human-readable name for the template.') }}</div>
                    </div>

                    <div class="col-md-4 mb-7">
                        <label for="subject" class="form-label fw-semibold mb-2 required">{{ __('Subject') }}</label>
                        <input type="text" class="form-control form-control-solid" id="subject" name="subject" value="{{ old('subject') }}" required>
                        <div class="invalid-feedback">{{ __('Please provide a subject.') }}</div>
                    </div>
                    <div class="col-md-8 mb-7">
                        <label for="content" class="form-label fw-semibold mb-2 required">{{ __('Content') }}</label>
                        <textarea class="form-control form-control-solid" name="content" id="content" rows="10" required>{{ old('content') }}</textarea>
                        <div class="invalid-feedback">{{ __('Please provide email content.') }}</div>
                        {{-- CKEditor initialization script should be placed in @section('custom-js-section') --}}
                    </div>

                    <div class="col-md-12 mb-7 d-flex justify-content-end">
                        <button class="btn btn-primary min-w-150px" type="submit">{{ __('Submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @else
        {{-- Optional: Message if user cannot create --}}
        {{--
        <div class="alert alert-warning">
            You do not have permission to create email templates.
        </div>
        --}}
    @endif

    {{-- List Templates Section --}}
    @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('list_email_templates')))
        <div class="card card-flush">
            <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                <div class="card-title">
                    <div class="d-flex align-items-center position-relative my-1">
                        <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                        <input type="text" id="template-search-input" class="form-control form-control-solid w-250px ps-12"
                               placeholder="Search Templates"/> {{-- Added ID for easier JS targeting --}}
                    </div>
                </div>
                <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                    {{-- Bulk Delete Button --}}
                    @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('delete_email_templates')))
                        <button type="button" class="btn btn-danger" id="bulk-delete-btn">Delete Selected</button>
                    @endif
                </div>
            </div>
            <div class="card-body pt-0">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="email_templates">
                    <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        {{-- Checkbox column only needed if bulk delete is allowed --}}
                        @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('delete_email_templates')))
                            <th class="w-10px pe-2">
                                <div class="form-check form-check-sm form-check-square form-check-custom form-check-solid me-3">
                                    <input class="form-check-input" type="checkbox" data-kt-check="true"
                                           data-kt-check-target="#email_templates .form-check-input" value="1"/> {{-- Changed target table ID --}}
                                </div>
                            </th>
                        @endif
                        <th class="min-w-150px">Slug</th> {{-- Increased min-width --}}
                        <th class="min-w-150px">Tags</th> {{-- Increased min-width --}}
                        <th class="min-w-150px">Label</th> {{-- Increased min-width --}}
                        <th class="min-w-200px">Subject</th> {{-- Increased min-width --}}
                        {{-- Actions column only needed if edit or delete is allowed --}}
                        @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('edit_email_templates') || auth()->user()->can('delete_email_templates')))
                            <th class="text-end min-w-150px">Actions</th> {{-- Adjusted min-width --}}
                        @endif
                    </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                    @forelse($emailTemplates as $item)
                        <tr>
                            {{-- Checkbox cell --}}
                            @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('delete_email_templates')))
                                <td>
                                    <div class="form-check form-check-sm form-check-square form-check-custom form-check-solid">
                                        <input class="form-check-input template-checkbox" type="checkbox" value="{{ $item->id }}"/> {{-- Added class --}}
                                    </div>
                                </td>
                            @endif
                            <td>
                                <div class="d-flex align-items-center">
                                    {{-- Allow clicking slug to edit if user has permission --}}
                                    @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('edit_email_templates')))
                                        <a href="{{ route('email.templates.edit', $item->id) }}" class="text-gray-800 text-hover-primary fs-6 fw-bold"> {{-- Adjusted font size --}}
                                            {{ $item->slug }}
                                        </a>
                                    @else
                                        <span class="text-gray-800 fs-6 fw-bold">{{ $item->slug }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-start pe-0"> {{-- Align left for better readability --}}
                                <span class="fw-bold">{{ $item->tags ?? 'N/A' }}</span>
                            </td>
                            <td class="text-start pe-0"> {{-- Align left --}}
                                <span class="fw-bold">{{ $item->label ?? 'N/A' }}</span>
                            </td>
                            <td class="text-start pe-0"> {{-- Align left --}}
                                <span class="fw-bold">{{ $item->subject ?? 'N/A' }}</span>
                            </td>

                            {{-- Actions Cell --}}
                            @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('edit_email_templates') || auth()->user()->can('delete_email_templates')))
                                <td class="text-end actions-cell">
                                    {{-- Edit Button --}}
                                    @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('edit_email_templates')))
                                        <a href="{{ route('email.templates.edit', $item->id) }}" class="btn btn-icon btn-active-light-primary w-30px h-30px me-1" title="Edit"> {{-- Icon button --}}
                                            <i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i>
                                        </a>
                                    @endif

                                    {{-- Delete Button --}}
                                    @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('delete_email_templates')))
                                        <form action="{{ route('email.templates.destroy', $item->id) }}" method="POST" style="display:inline-block;" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-icon btn-active-light-danger w-30px h-30px delete-button" title="Delete"> {{-- Icon button, changed class --}}
                                                <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        {{-- Calculate colspan dynamically --}}
                        @php
                            $colspan = 4; // Slug, Tags, Label, Subject
                            if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('delete_email_templates'))) $colspan++; // Checkbox
                            if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('edit_email_templates') || auth()->user()->can('delete_email_templates'))) $colspan++; // Actions
                        @endphp
                        <tr>
                            <td colspan="{{ $colspan }}" class="text-center text-gray-600 py-10">
                                {{ __('No email templates found.') }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <div class="alert alert-danger text-center">
                    {{ __('You do not have permission to view email templates.') }}
                </div>
            </div>
        </div>
    @endif

@endsection

@section('custom-js-section')
    {{-- Only include scripts if the user can view the list --}}
    @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('list_email_templates')))
        {{-- Include DataTables library if not already globally included --}}
        {{-- <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script> --}}
        {{-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"> --}}
        {{-- Include SweetAlert2 if not already globally included --}}
        {{-- <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}

        <script>
            // Helper function for CSRF token
            function getCsrfToken() {
                return $('meta[name="csrf-token"]').attr('content');
            }

            $(document).ready(function () {
                // Initialize CKEditor if the form exists
                @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('create_email_templates')))
                if(document.querySelector('#content')) {
                    ClassicEditor.create(document.querySelector('#content'), {
                        // Optional CKEditor configuration
                        // toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote' ],
                    })
                        .catch(error => {
                            console.error("CKEditor Error:", error);
                        });
                }
                @endif

                // Initialize DataTable
                const table = $("#email_templates").DataTable({
                    info: false, // Hide "Showing 1 to X of Y entries"
                    pageLength: 10,
                    lengthChange: false, // Hide page length selector
                    order: [], // Disable initial sorting
                    columnDefs: [
                        // Disable sorting for checkbox and actions columns based on permissions
                            @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('delete_email_templates')))
                        { orderable: false, targets: 0 }, // Checkbox column index 0
                            @endif
                            @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('edit_email_templates') || auth()->user()->can('delete_email_templates')))
                            @php
                                // Calculate the actions column index
                                $actionsColIndex = 4; // Starts after Subject
                                if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('delete_email_templates'))) $actionsColIndex++; // Add 1 if checkbox exists
                            @endphp
                        { orderable: false, targets: {{ $actionsColIndex }} }, // Actions column index
                        @endif
                    ],
                    searching: true,
                    // Integrate search with the custom input
                    // dom: '<"top"f>rt<"bottom"lip>', // Basic structure if needed, often Metronic handles this
                });

                // Custom search input functionality
                $('#template-search-input').on('keyup', function () {
                    table.search($(this).val()).draw();
                });


                // --- Delete single item logic ---
                @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('delete_email_templates')))
                // Use event delegation for delete buttons
                $('#email_templates').on('click', '.delete-button', function(event) {
                    event.preventDefault();
                    const form = $(this).closest('.delete-form'); // Get the form associated with the button

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33', // Red for delete
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit(); // Submit the form if confirmed
                        }
                    });
                });
                @endif

                // --- Bulk delete logic ---
                @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('delete_email_templates')))
                // Select/Deselect all checkbox functionality
                const headerCheckbox = $('[data-kt-check="true"]');
                const itemCheckboxes = $('.template-checkbox'); // Use specific class

                headerCheckbox.on('change', function () {
                    const isChecked = $(this).prop('checked');
                    itemCheckboxes.prop('checked', isChecked);
                });

                // Uncheck header if any item is unchecked
                itemCheckboxes.on('change', function() {
                    if (!$(this).prop('checked')) {
                        headerCheckbox.prop('checked', false);
                    } else {
                        // Optionally check header if all items are checked (can be intensive)
                        if (itemCheckboxes.length === itemCheckboxes.filter(':checked').length) {
                            headerCheckbox.prop('checked', true);
                        }
                    }
                });


                $('#bulk-delete-btn').on('click', function () {
                    const selectedIds = [];
                    // Use the specific class to select checkboxes
                    $('.template-checkbox:checked').each(function () {
                        selectedIds.push($(this).val());
                    });

                    if (selectedIds.length === 0) {
                        Swal.fire({
                            text: 'Please select at least one template to delete.',
                            icon: 'warning',
                            buttonsStyling: false,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn fw-bold btn-primary",
                            }
                        });
                        return;
                    }

                    Swal.fire({
                        title: 'Are you sure?',
                        text: `You are about to delete ${selectedIds.length} template(s). You won't be able to revert this!`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete them!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '{{ route('email.templates.bulk-delete') }}', // Make sure this route is defined and accepts POST/DELETE
                                type: 'POST', // Or 'DELETE' if your route is set up for it
                                data: {
                                    ids: selectedIds,
                                    _token: getCsrfToken(), // Use helper function
                                    // _method: 'DELETE' // Add this if using POST to simulate DELETE
                                },
                                dataType: 'json', // Expect JSON response
                                success: function (response) {
                                    Swal.fire({
                                        title: response.title || 'Deleted!', // Use response title or default
                                        text: response.message,
                                        icon: response.icon || 'success', // Use response icon or default
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, got it!",
                                        customClass: {
                                            confirmButton: "btn fw-bold btn-primary",
                                        }
                                    }).then(() => {
                                        // Reload page or redraw table (more efficient)
                                        location.reload();
                                        // table.draw(); // Alternative to reload if deletion happens serverside
                                    });
                                },
                                error: function (xhr) {
                                    console.error("Bulk Delete Error:", xhr);
                                    const response = xhr.responseJSON || {};
                                    Swal.fire({
                                        title: response.title || 'Error!',
                                        text: response.message || 'An error occurred while deleting templates.',
                                        icon: 'error',
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, got it!",
                                        customClass: {
                                            confirmButton: "btn fw-bold btn-danger",
                                        }
                                    });
                                }
                            });
                        }
                    });
                });
                @endif // End bulk delete permission check

                // Basic Bootstrap form validation (if using 'needs-validation')
                // Fetch all the forms we want to apply custom Bootstrap validation styles to
                var forms = document.querySelectorAll('.needs-validation')
                // Loop over them and prevent submission
                Array.prototype.slice.call(forms)
                    .forEach(function (form) {
                        form.addEventListener('submit', function (event) {
                            if (!form.checkValidity()) {
                                event.preventDefault()
                                event.stopPropagation()
                            }
                            form.classList.add('was-validated')
                        }, false)
                    })

            }); // End document ready
        </script>
    @endif // End list permission check
@stop
