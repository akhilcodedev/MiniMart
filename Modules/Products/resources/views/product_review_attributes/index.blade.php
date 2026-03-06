@extends('base::layouts.mt-main')

@section('content')

    <!--begin::Container-->
    <div class="container-xxl" id="kt_content_container">

        <!--begin::Card-->
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <!--begin::Card title-->
                <div class="card-title">
                    <h1>Product Review Attributes</h1>
                </div>
                <!--begin::Card title-->
                <!--begin::Card toolbar-->
                <div class="card-toolbar">
                    <!--begin::Toolbar-->
                    <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                    {{-- Check permission for creating attributes --}}
                    @if(auth()->user()->is_super_admin || auth()->user()->can('create_product_review_attributes'))
                        <!--begin::Add user-->
                            <a href="{{ route('product_review_attributes.create') }}" class="btn btn-primary">
                                <!--begin::Svg Icon | path: icons/duotune/arrows/arr075.svg-->
                                <span class="svg-icon svg-icon-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none">
                                    <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2" rx="1"
                                          transform="rotate(-90 11.364 20.364)" fill="black" />
                                    <rect x="4.36396" y="11.364" width="16" height="2" rx="1" fill="black" />
                                </svg>
                            </span>
                                <!--end::Svg Icon-->
                                Add Attribute
                            </a>
                            <!--end::Add user-->
                        @endif
                    </div>
                    <!--end::Toolbar-->
                </div>
                <!--end::Card toolbar-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body py-4">
                <!--begin::Table-->
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
                    <!--begin::Table head-->
                    <thead>
                    <!--begin::Table row-->
                    <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                        <th class="min-w-25px">ID</th>
                        <th class="min-w-125px">Name</th>
                        <th class="min-w-125px">Label</th>
                        {{-- Conditionally show Actions header --}}
                        @if(auth()->user()->is_super_admin || auth()->user()->can('edit_product_review_attributes') || auth()->user()->can('delete_product_review_attributes'))
                            <th class="text-end min-w-100px">Actions</th>
                        @endif
                    </tr>
                    <!--end::Table row-->
                    </thead>
                    <!--end::Table head-->
                    <!--begin::Table body-->
                    <tbody class="text-gray-600 fw-bold">
                    @foreach ($attributes as $attribute)
                        <tr>
                            <td>{{ $attribute->id }}</td>
                            <td>{{ $attribute->name }}</td>
                            <td>{{ $attribute->label }}</td>
                            {{-- Conditionally show Actions cell --}}
                            @if(auth()->user()->is_super_admin || auth()->user()->can('edit_product_review_attributes') || auth()->user()->can('delete_product_review_attributes'))
                                <td class="text-end">
                                    {{-- Check permission for editing attributes --}}
                                    @if(auth()->user()->is_super_admin || auth()->user()->can('edit_product_review_attributes'))
                                        <a href="{{ route('product_review_attributes.edit', $attribute->id) }}"
                                           class="btn btn-sm btn-light btn-active-light-primary me-2">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    @endif

                                    {{-- Check permission for deleting attributes --}}
                                    @if(auth()->user()->is_super_admin || auth()->user()->can('delete_product_review_attributes'))
                                        <form action="{{ route('product_review_attributes.destroy', $attribute->id) }}"
                                              method="POST" style="display:inline">
                                            @csrf
                                            @method('DELETE')
                                            {{-- Consider adding confirmation (e.g., onclick="return confirm('Are you sure?');") --}}
                                            <button type="submit" class="btn btn-sm btn-light btn-active-light-danger">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                    <!--end::Table body-->
                </table>
                <!--end::Table-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->

    </div>
    <!--end::Container-->

@endsection
