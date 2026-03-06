@extends('base::layouts.mt-main')
@section('content')
    <div class="container">
        <h1>Product Options</h1>
        <a href="{{ route('product.variant.options.create') }}" class="btn btn-primary mb-3">Add New Option</a>
        <div class="d-flex align-items-center position-relative py-10">
            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
            <input type="text" id="product_variant_option" name="search" class="form-control form-control-solid w-250px ps-12" placeholder="Search Option">
        </div>

        <table class="table table-bordered" id="product_options_list_filter_table">
            <thead>
            <tr>
                <th>#</th>
                <th>Code</th>
                <th>Name</th>
                <th>Active</th>
                <th>Created By</th>
                <th>Updated At</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($productOptions as $option)
                <tr>
                    <td>{{ $option->id }}</td>
                    <td>{{ $option->code }}</td>
                    <td>{{ $option->name }}</td>
                    <td>
                        @if($option->active)
                            <span class="label label-lg font-weight-bold label-light-success label-inline">Yes</span>
                        @else
                            <span class="label label-lg font-weight-bold label-light-danger label-inline">No</span>
                        @endif
                    </td>
                    <td>{{ $option->created_by }}</td>
                    <td>{{ $option->updated_at ? $option->updated_at->format('F d, Y, h:i:s A') : 'N/A' }}</td>
                    <td>
                        <a href="{{ route('product.variant.options.edit', $option->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('product.variant.options.destroy', $option->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger delete-btn">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('custom-js-section')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready( function () {
            var KTDatatablesServerSide = function () {
                var dt;

                var initDatatable = function () {
                    dt = $("#product_options_list_filter_table").DataTable();

                    $('#product_variant_option').on('keyup', function () {
                        dt.draw();
                    });
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
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.delete-btn').on('click', function(event) {
                event.preventDefault();
                var form = $(this).closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You won\'t be able to revert this!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection