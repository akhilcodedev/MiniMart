@php
    function userCan($permission) {
        if (!auth()->check()) {
            return false;
        }
        if (auth()->user()->is_super_admin == 1) {
            return true;
        }
        return auth()->user()->can($permission);
    }

    $canViewActions = userCan('edit_payment_methods') || userCan('delete_payment_methods');
@endphp

@extends('base::layouts.mt-main')

@section('title', 'Payment Methods')

@section('content')
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">
            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <div class="d-flex align-items-center position-relative my-1">
                            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                            <input type="text" data-kt-item-table-filter="search" class="form-control form-control-solid w-250px ps-13" placeholder="Search Payment Methods" />
                        </div>
                    </div>
                    <div class="card-toolbar">
                        <div class="d-flex justify-content-end" data-kt-item-table-toolbar="base">
                            @if(userCan('create_payment_methods'))
                                <a href="{{ route('payment.create') }}" class="btn btn-primary">Add Payment Method</a>
                            @endif
                        </div>
                        @if(userCan('delete_payment_methods'))
                            <div class="d-flex justify-content-end align-items-center d-none" data-kt-payment-method-table-toolbar="selected">
                                <div class="fw-bold me-5">
                                    <span class="me-2" data-kt-payment-method-table-select="selected_count"></span>Selected</div>
                                <button type="button" class="btn btn-danger" data-kt-payment-method-table-select="delete_selected">Delete Selected</button>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card-body pt-0">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_items_table">
                        <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th>Name</th>
                            <th>Code</th>
                            <th>Value</th>
                            <th>Sort Order</th>
                            <th>Test Mode</th>
                            <th>Online</th>
                            <th>Status</th>
                            @if($canViewActions)
                                <th class="text-end">Actions</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                        @forelse($paymentMethods as $method)
                            <tr>
                                <td>{{ $method->name }}</td>
                                <td>{{ $method->code }}</td>
                                <td>
                                    @php
                                        $firstValue = $method->attributes?->first()?->value;
                                    @endphp
                                    {{ $firstValue ? \Illuminate\Support\Str::limit($firstValue, 15) : 'N/A' }}
                                </td>
                                <td>{{ $method->sort_order }}</td>
                                <td>
                                    <span class="badge {{ $method->test_mode ? 'badge-light-success' : 'badge-light-warning' }}">
                                        {{ $method->test_mode ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                     <span class="badge {{ $method->is_online ? 'badge-light-success' : 'badge-light-danger' }}">
                                        {{ $method->is_online ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                     <span class="badge {{ $method->is_active ? 'badge-light-success' : 'badge-light-danger' }}">
                                        {{ $method->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                @if($canViewActions)
                                    <td class="text-end">
                                        @if(userCan('edit_payment_methods'))
                                            <a href="{{ route('payment.edit', $method->id) }}" class="btn btn-sm btn-light btn-active-light-primary me-2">
                                                <i class="fas fa-edit"></i> {{ __('Edit') }}
                                            </a>
                                        @endif

                                        @if(userCan('delete_payment_methods'))
                                            <form action="{{ route('payment.destroy', $method->id) }}" method="POST" class="delete-form d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light btn-active-light-danger delete-button">
                                                    <i class="fas fa-trash"></i> {{ __('Delete') }}
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canViewActions ? 8 : 7 }}" class="text-center">No Payment Methods Found</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-js-section')
    {{-- Ensure jQuery, DataTables JS/CSS, and SweetAlert JS/CSS are loaded --}}
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <script>
        $(document).ready(function() {

            const canViewActions = {{ $canViewActions ? 'true' : 'false' }};
            const actionsColIdx = 7; // Index of the actions column if present (0-based)

            let dtColumnDefs = [];
            if (canViewActions) {
                dtColumnDefs.push({ orderable: false, targets: actionsColIdx });
            }

            const table = $('#kt_items_table').DataTable({
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [[0, 'asc']],
                columnDefs: dtColumnDefs,
                searching: true,
                paging: true,
                info: true,
            });

            $('[data-kt-item-table-filter="search"]').on('keyup', function () {
                table.search($(this).val()).draw();
            });

            $('#kt_items_table').on('submit', 'form.delete-form', function(event) {
                event.preventDefault();
                const form = this;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this payment method!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, cancel!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();

                        /* Optional AJAX Delete:
                        $.ajax({
                            url: form.action,
                            type: 'POST',
                            data: $(form).serialize(),
                            success: function(response) {
                                Swal.fire('Deleted!', 'Payment method has been deleted.', 'success');
                                table.row($(form).closest('tr')).remove().draw(false);
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', 'Could not delete payment method.', 'error');
                                console.error("Delete error:", xhr.responseText);
                            }
                        });
                        */
                    }
                });
            });

            /* Bulk Delete Placeholder:
            const deleteSelectedButton = document.querySelector('[data-kt-payment-method-table-select="delete_selected"]');
            if (deleteSelectedButton) {
                deleteSelectedButton.addEventListener('click', function () {
                    const selectedCheckboxes = table.rows().nodes().to$().find('.form-check-input:checked');
                    const selectedIds = selectedCheckboxes.map(function() { return $(this).val(); }).get();

                    if (selectedIds.length === 0) {
                       Swal.fire('No selection', 'Please select at least one payment method to delete.', 'info');
                       return;
                    }

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert the selected payment methods!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete them!',
                        cancelButtonText: 'No, cancel!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // AJAX call to a bulk delete endpoint
                            // e.g., POST /payment/bulk-delete with { ids: selectedIds, _token: '...' }
                            // On success: remove selected rows from table, update count, hide toolbar
                        }
                    });
                });
            }
            */

        });
    </script>
@endsection
