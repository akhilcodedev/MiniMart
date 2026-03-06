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

@section('content')
<div class="post d-flex flex-column-fluid" id="kt_post">
    <div id="kt_content_container" class="container-xxl">
        <div class="card card-flush">
            <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                <div class="card-title">
                    <div class="d-flex align-items-center position-relative my-1">
                        <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                        <input type="text" class="form-control form-control-solid w-250px ps-12"
                            placeholder="Search URL Rewrites" />
                    </div>
                </div>
                <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                    <!-- Removed Bulk Delete Button -->
                    <!-- Removed Add Page Button, Adjusted text to Create URL Rewrite-->
                    {{-- <a href="{{ route('cms.pages.create') }}" class="btn btn-primary">Add Page</a> --}}
                </div>
            </div>
            <div class="card-body pt-0">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_ecommerce_products_table">
                    <thead>
                        <tr class="text-start text-gray-900 fw-bold fs-7 text-uppercase gs-0">
                            {{-- <th class="w-10px pe-2">
                                <div
                                    class="form-check form-check-sm form-check-square form-check-custom form-check-solid me-3">
                                    <input class="form-check-input" type="checkbox" data-kt-check="true"
                                        data-kt-check-target="#kt_ecommerce_products_table .form-check-input"
                                        value="1" />
                                </div>
                            </th> --}}
                            <th class="min-w-100px">ID</th>
                            <th class="min-w-100px">Entity Type</th>
                            <th class="min-w-100px">Entity ID</th>
                            <th class="min-w-100px">Request Path</th>
                            <th class="min-w-100px">Target Path</th>
                            <th class="text-end min-w-100px">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-900">
                        @foreach($urlRewrites as $urlRewrite)
                            <tr>
                                {{-- <td>
                                    <div
                                        class="form-check form-check-sm form-check-square form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" value="{{ $urlRewrite->id }}" />
                                    </div>
                                </td> --}}
                                <td class="text-gray-900">{{ $urlRewrite->id }}</td>
                                <td class="text-gray-900">{{ $urlRewrite->entity_type }}</td>
                                <td class="text-gray-900">{{ $urlRewrite->entity_id }}</td>
                                <td class="text-gray-900">{{ $urlRewrite->request_path }}</td>
                                <td class="text-gray-900">{{ $urlRewrite->target_path }}</td>

                                <!-- Removed Actions Column -->
                                <td class="text-end">
                                    @if(auth()->user()->is_super_admin || auth()->user()->can('edit_url_rewrites'))

                                    <a href="{{ route('urlrewrite.edit', $urlRewrite->id) }}"
                                        class="btn btn-sm btn-light btn-active-light-primary me-2">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    @endif

                                    {{-- <form action="" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="btn btn-sm btn-light btn-active-light-danger delete-banner"
                                            style="border:none; background:none;">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form> --}}
                                </td>
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
    $(document).ready(function () {
        const table = $("#kt_ecommerce_products_table").DataTable({
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            order: [[0, 'asc']],
            columnDefs: [

            ],
            searching: true
        });


        $('input[placeholder="Search URL Rewrites"]').on('keyup', function () {
            table.search($(this).val()).draw();
        });

    });

</script>
