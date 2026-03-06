@extends('base::layouts.mt-main')
@section('content')
    @php
        $authUser = auth()->user();
        $isSuperAdmin = $authUser && $authUser->is_super_admin == 1;
    @endphp
    <style>
        .image-input-placeholder {
            background-image: url("https://preview.keenthemes.com/html/metronic/docs/assets/media/svg/avatars/blank.svg");
        }

        [data-bs-theme="dark"] .image-input-placeholder {
            background-image: url("https://preview.keenthemes.com/html/metronic/docs/assets/media/svg/avatars/blank.svg");
        }
    </style>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" rel="stylesheet"/>
    <div class="container">
        <h2>Category Tree</h2>
        <div class="card mt-10 p-5 d-flex  mw-1200px">
            <div class="card-body d-flex gap-5">
                @if($isSuperAdmin || $authUser->can('create_categories'))

                <button id="addRootCategory" class="btn btn-light-primary">Add Root Category</button>
                @endif
                    @if($isSuperAdmin || $authUser->can('create_categories'))

                    <button id="addSubcategory" class="btn btn-light-secondary">Add Subcategory</button>
                    @endif
                        @if($isSuperAdmin || $authUser->can('edit_categories'))

                        <button id="editCategory" class="btn btn-light-warning ms-auto"> <i class="ki-outline ki-message-edit ">
                                @endif
                </i> Edit Category</button>
                            @if($isSuperAdmin || $authUser->can('delete_categories'))

                            <button id="deleteCategory" class="btn btn-light-danger"><i class="ki-outline ki-trash"></i> Delete Category</button>
                        @endif
            </div>
        </div>
        @if($isSuperAdmin || $authUser->can('list_categories'))

        <form id="categoryForm" style="display: none;" class="card mt-10 p-5 mw-1200px mt-3">
            @csrf
            <div class="row g-6">
                <div class="d-flex gap-6">
                    <div class="form-check form-switch form-check-custom form-check-success form-check-solid">
                        <input class="form-check-input" type="checkbox" value="" id="is_enabled" name="is_enabled">
                        <label class="form-check-label" for="is_enabled">
                            Enable Category
                        </label>
                    </div>
                    <div class="form-check form-switch form-check-custom form-check-success form-check-solid">
                        <input class="form-check-input" type="checkbox" value="" id="included_in_menu" name="included_in_menu">
                        <label class="form-check-label" for="included_in_menu">
                            Include in Menu
                        </label>
                    </div>
                    <button type="button" id="saveCategory" class="btn btn-primary ms-auto">Save</button>
                </div>
                <div class="col-12">
                    <label class="form-label" for="categoryName">Category Name</label>
                    <input type="text" id="categoryName" placeholder="Category Name" class="form-control">
                </div>
                <input type="hidden" id="parentCategoryId">
                <input type="hidden" id="editCategoryId">
            </div>
            <div class="accordion my-4" id="kt_accordion_1">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="kt_accordion_1_header_1">
                        <button class="accordion-button fs-4 fw-semibold" type="button" data-bs-toggle="collapse"
                                data-bs-target="#kt_accordion_1_body_1" aria-expanded="true"
                                aria-controls="kt_accordion_1_body_1">
                            Content
                        </button>
                    </h2>
                    <div id="kt_accordion_1_body_1" class="accordion-collapse collapse"
                         aria-labelledby="kt_accordion_1_header_1" data-bs-parent="#kt_accordion_1">
                        <div class="accordion-body">
                            <div class="d-flex gap-10">
                                <label class="form-label fw-semibold d-block mb-4"> Banner image</label>
                                <div class="image-input image-input-empty image-input-placeholder"
                                     data-kt-image-input="true">
                                    <div class="image-input-wrapper w-125px h-125px"></div>
                                    <label
                                        class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="change"
                                        data-bs-toggle="tooltip"
                                        data-bs-dismiss="click"
                                        title="Change avatar">
                                        <i class="ki-duotone ki-pencil fs-6"><span class="path1"></span><span
                                                class="path2"></span></i>
                                        <input type="file" name="banner_image" id="banner_image"
                                               accept=".png, .jpg, .jpeg"/>
                                        <input type="hidden" name="avatar_remove"/>
                                    </label>
                                    <span
                                        class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="cancel"
                                        data-bs-toggle="tooltip"
                                        data-bs-dismiss="click"
                                        title="Cancel avatar">
                                <i class="ki-outline ki-cross fs-3"></i>
                            </span>
                                    <span
                                        class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="remove"
                                        data-bs-toggle="tooltip"
                                        data-bs-dismiss="click"
                                        title="Remove avatar">
                                <i class="ki-outline ki-cross fs-3"></i>
                            </span>
                                </div>
                                <label class="form-label fw-semibold d-block mb-4"> Category image</label>
                                <div class="image-input image-input-empty image-input-placeholder"
                                     data-kt-image-input="true">
                                    <div class="image-input-wrapper w-125px h-125px"></div>
                                    <label
                                        class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="change"
                                        data-bs-toggle="tooltip"
                                        data-bs-dismiss="click"
                                        title="Change avatar">
                                        <i class="ki-duotone ki-pencil fs-6"><span class="path1"></span><span
                                                class="path2"></span></i>
                                        <input type="file" name="category_image" id="category_image"
                                               accept=".png, .jpg, .jpeg"/>
                                        <input type="hidden" name="avatar_remove"/>
                                    </label>
                                    <span
                                        class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="cancel"
                                        data-bs-toggle="tooltip"
                                        data-bs-dismiss="click"
                                        title="Cancel avatar">
                                <i class="ki-outline ki-cross fs-3"></i>
                            </span>
                                    <span
                                        class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
                                        data-kt-image-input-action="remove"
                                        data-bs-toggle="tooltip"
                                        data-bs-dismiss="click"
                                        title="Remove avatar">
                                <i class="ki-outline ki-cross fs-3"></i>
                            </span>
                                </div>
                            </div>
                            <div class="d-flex mt-5">
                                 <div id="banner_image_preview_container" class="col-md-5"></div>
                                 <div id="category_image_preview_container" class="col-md-5"></div>
                            </div>
                            <div class="col-12 mt-5">
                                <label class="form-label" for="categoryDescription">Description</label>
                                <textarea id="categoryDescription" placeholder="Description" name="description" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="accordion my-4" id="kt_accordion_2">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="kt_accordion_2_header_2">
                        <button class="accordion-button fs-4 fw-semibold" type="button" data-bs-toggle="collapse"
                                data-bs-target="#kt_accordion_2_body_2" aria-expanded="true"
                                aria-controls="kt_accordion_2_body_2">
                            SEO
                        </button>
                    </h2>
                    <div id="kt_accordion_2_body_2" class="accordion-collapse collapse"
                         aria-labelledby="kt_accordion_2_header_2" data-bs-parent="#kt_accordion_2">
                        <div class="accordion-body">
                            <div class="col-12">
                                <label class="form-label" for="meta_keywords">Meta Key</label>
                                <input type="text" id="meta_keywords" placeholder="Meta Key" class="form-control" name="meta_keywords">
                            </div>
                            <div class="col-12 mt-6">
                                <label class="form-label" for="meta_title">Meta Title</label>
                                <input type="text" id="meta_title" placeholder="Meta Title" class="form-control" name="meta_title">
                            </div>
                            <div class="col-12 mt-6">
                                <label class="form-label" for="meta_description">Meta Description</label>
                                <input type="text" id="meta_description" placeholder="Meta Description" class="form-control" name="meta_description">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div id="category-tree" class="card mt-10 p-5 mw-1200px"></div>
        @endif
    </div>
@endsection
@section('custom-js-section')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            const categories = @json($categories);

            function buildTree(categories) {
                return categories.map(category => ({
                    id: category.id,
                    text: category.name,
                    children: buildTree(category.children || [])
                }));
            }

            // Initialize the category tree with drag and drop enabled
            $('#category-tree').jstree({
                'core': {
                    'data': buildTree(categories),
                    'check_callback': true,
                    'themes': {
                        'responsive': false
                    },
                },
                'plugins': ['dnd'],  // Add the drag-and-drop plugin
            });

            // Event handler when a node is moved
            $('#category-tree').on('move_node.jstree', function (e, data) {
                const movedNodeId = data.node.id;
                const newParentId = data.parent;
                const newLevel = data.position; // Position in the tree

                // AJAX request to update the parent_id and level of the moved category
                $.ajax({
                    url: '/categories/update-level',  // Define the route for updating category level
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        category_id: movedNodeId,
                        parent_id: newParentId,
                        level: newLevel
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: 'Category updated successfully.',
                            confirmButtonText: 'OK'
                        });
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON.message || 'An error occurred while updating.',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });

            $('#addRootCategory').click(function () {
                $('#categoryForm').show();
                $('#parentCategoryId').val('');
                $('#editCategoryId').val('');
                $('#categoryName').val('');
                $('#categoryDescription').val('');
            });

            $('#addSubcategory').click(function () {
                const selectedNode = $('#category-tree').jstree('get_selected', true);
                if (selectedNode.length) {
                    $('#categoryForm').show();
                    $('#parentCategoryId').val(selectedNode[0].id);
                    $('#editCategoryId').val('');
                    $('#categoryName').val('');
                    $('#categoryDescription').val('');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Please select a category first!',
                        confirmButtonText: 'OK'
                    });
                }
            });

            $('#editCategory').click(function () {
                const selectedNode = $('#category-tree').jstree('get_selected', true);

                if (selectedNode.length) {
                    const categoryId = selectedNode[0].id;
                    const url = `{{ route('get.categoryById', ':id') }}`.replace(':id', categoryId);

                    $.ajax({
                        url: url,
                        type: 'GET',
                        success: function (response) {
                            if (response.status) {
                                const data = response.data;
                                $('#categoryForm').show();
                                $('#editCategoryId').val(data.id);
                                $('#parentCategoryId').val(data.parent_id || '');
                                $('#categoryName').val(data.name);
                                $('#categoryDescription').val(data.description || '');

                                if(data.is_enabled == 1){
                                    $('#is_enabled').attr('checked', true);
                                }
                                if(data.included_in_menu == 1){
                                    $('#included_in_menu').attr('checked', true);
                                }

                                if (data.banner_image) {
                                    const bannerImagePath = `/storage/${data.banner_image}`;
                                    const bannerImagePreview = $('<img>', {
                                        src: bannerImagePath,
                                        alt: 'Banner Image',
                                        style: 'width: 200px; height : 100px'
                                    });
                                    $('#banner_image_preview_container').html(bannerImagePreview);
                                } else {
                                    $('#banner_image_preview_container').empty();
                                }

                                if (data.category_image) {
                                    const categoryImagePath = `/storage/${data.category_image}`;
                                    const categoryImagePreview = $('<img>', {
                                        src: categoryImagePath,
                                        alt: 'Category Image',
                                        style: 'width: 200px; height : 100px'
                                    });
                                    $('#category_image_preview_container').html(categoryImagePreview);
                                } else {
                                    $('#category_image_preview_container').empty();
                                }

                                $('#meta_title').val(data.meta.meta_title || '');
                                $('#meta_keywords').val(data.meta.meta_keywords || '');
                                $('#meta_description').val(data.meta.meta_description || '');
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'Failed to fetch category details.',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function () {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to fetch category details.',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Please select a category first!',
                        confirmButtonText: 'OK'
                    });
                }
            });


            $('#saveCategory').click(function () {
                const formData = new FormData();

                formData.append('name', $('#categoryName').val());
                formData.append('description', $('#categoryDescription').val());
                formData.append('parent_id', $('#parentCategoryId').val());
                formData.append('meta_keywords', $('#meta_keywords').val());
                formData.append('meta_title', $('#meta_title').val());
                formData.append('meta_description', $('#meta_description').val());
                formData.append('is_enabled', $('#is_enabled').is(':checked') ? 1 : 0);
                formData.append('included_in_menu', $('#included_in_menu').is(':checked') ? 1 : 0);

                if ($('#banner_image')[0].files[0]) {
                    formData.append('banner_image', $('#banner_image')[0].files[0]);
                }

                if ($('#category_image')[0].files[0]) {
                    formData.append('category_image', $('#category_image')[0].files[0]);
                }

                formData.append('_token', '{{ csrf_token() }}');

                const editId = $('#editCategoryId').val();
                const url = editId
                    ? `/categories/${editId}/update`
                    : '{{ route('categories.store') }}';
                const method = editId ? 'POST' : 'POST';


                if (!formData.get('name')) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Category name is required.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                $.ajax({
                    url: url,
                    method: method,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message || 'Category saved successfully!',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function (xhr) {
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            let errors = '';
                            $.each(xhr.responseJSON.errors, function (key, value) {
                                errors += value[0] + '\n';
                            });
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errors || 'An error occurred',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON.message || 'An error occurred',
                                confirmButtonText: 'OK'
                            });
                        }
                    }
                });
            });


            $('#deleteCategory').click(function () {
                const selectedNode = $('#category-tree').jstree('get_selected', true);
                if (selectedNode.length) {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'You want to delete this category?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `/categories/${selectedNode[0].id}/delete`,
                                method: 'DELETE',
                                data: {_token: '{{ csrf_token() }}'},
                                success: function (response) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: response.message || 'Category has been deleted.',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        location.reload();
                                    });
                                },
                                error: function (xhr) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: xhr.responseJSON.message || 'An error occurred',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            });
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Please select a category first!',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });


    </script>
@endsection

