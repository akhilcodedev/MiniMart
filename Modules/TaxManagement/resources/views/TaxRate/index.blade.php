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

    $canViewActions = userCan('edit_tax_rates') || userCan('delete_tax_rates');
@endphp

@extends('base::layouts.mt-main')

@section('title', 'Tax Rates')

@section('content')
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">
            <div class="card">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <div class="d-flex align-items-center position-relative my-1">
                            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                            <input type="text" id="tax_rate_search" class="form-control form-control-solid w-250px ps-13" placeholder="Search Tax Rates" />
                        </div>
                    </div>
                    <div class="card-toolbar">
                        @if(userCan('create_tax_rates'))
                            <a href="{{ route('tax-rates.create') }}" class="btn btn-primary btn-sm">Add Tax Rate</a>
                        @endif
                    </div>
                </div>
                <div class="card-body pt-0">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_tax_rates_table">
                        <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th>ID</th>
                            <th>Tax Class</th>
                            <th>Country</th>
                            <th>State</th>
                            <th>Rate (%)</th>
                            <th>Type</th>
                            @if($canViewActions)
                                <th class="text-end">Actions</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                        @forelse ($taxRates as $taxRate)
                            <tr>
                                <td>{{ $taxRate->id }}</td>
                                <td>{{ $taxRate->taxClass?->name ?? 'N/A' }}</td>
                                <td>{{ $taxRate->country }}</td>
                                <td>{{ $taxRate->state ?? 'N/A' }}</td>
                                <td>{{ rtrim(rtrim(number_format($taxRate->rate, 4), '0'), '.') }}%</td>
                                <td>{{ ucwords(str_replace('_', ' ', $taxRate->type)) }}</td>

                                @if($canViewActions)
                                    <td class="text-end">
                                        @if(userCan('edit_tax_rates'))
                                            <a href="{{ route('tax-rates.edit', $taxRate) }}" class="btn btn-sm btn-light btn-active-light-primary me-2"> <i class="fas fa-edit"></i>  Edit</a>
                                        @endif

                                        @if(userCan('delete_tax_rates'))
                                            <form action="{{ route('tax-rates.destroy', $taxRate) }}" method="POST" class="delete-form d-inline">
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
                                <td colspan="{{ $canViewActions ? 7 : 6 }}" class="text-center">No Tax Rates Found</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <script>
        $(document).ready(function() {
            const canViewActions = {{ $canViewActions ? 'true' : 'false' }};
            const actionsColIdx = 6;

            let dtColumnDefs = [];
            if (canViewActions) {
                dtColumnDefs.push({ orderable: false, targets: actionsColIdx });
            }

            const table = $('#kt_tax_rates_table').DataTable({
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [[0, 'asc']],
                columnDefs: dtColumnDefs,
                searching: true,
                paging: true,
                info: true,
            });

            $('#tax_rate_search').on('keyup', function () {
                table.search($(this).val()).draw();
            });

            $('#kt_tax_rates_table').on('submit', 'form.delete-form', function(event) {
                event.preventDefault();
                const form = this;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this tax rate!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                        /* Optional AJAX Delete:
                        $.ajax({
                            url: form.action, type: 'POST', data: $(form).serialize(),
                            success: function(response) {
                               Swal.fire('Deleted!','Tax rate has been deleted.','success');
                               table.row($(form).closest('tr')).remove().draw(false);
                            },
                            error: function(xhr) { Swal.fire('Error!', 'Could not delete tax rate.', 'error'); }
                        });
                        */
                    }
                });
            });

        });
    </script>
@endsection
