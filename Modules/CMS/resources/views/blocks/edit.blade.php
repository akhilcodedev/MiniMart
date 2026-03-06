@extends('base::layouts.mt-main')

@section('content')
    <div class="card">
        <div class="card-header mt-10">
            <h3>Edit Block</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('cms-blocks.update', $cms_static_block->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="form-group col-md-4">
                        <label class="form-label" for="title">Title</label>
                        <input type="text" name="title" id="title" class="form-control" placeholder="Enter block title" value="{{ $cms_static_block->title }}"  required>
                    </div>

                    <div class="form-group col-md-4">
                        <label class="form-label" for="identifier">Identifier</label>
                        <input type="text" name="identifier" class="form-control" placeholder="Enter unique identifier" id="identifier" value="{{ $cms_static_block->identifier }}" disabled>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="form-label" for="is_active">Status</label>
                        <select name="is_active" class="form-control" id="is_active">
                            <option value="1" {{ $cms_static_block->is_active == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ $cms_static_block->is_active == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div id="kt_docs_repeater_basic">
                    <div class="form-group">
                        <div data-repeater-list="contents">
                            <div data-repeater-item>
                                <div class="form-group row">
                                    <div class="col-md-12">
                                        <label class="form-label" for="content_data">Content:</label>
                                        <textarea name="content_data" class="form-control mb-2" rows="3" id="content_data" placeholder="Enter content">{{ old('content', $cms_static_block->content) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-success">Update Block</button>
                    <a href="{{ route('cms-blocks.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@stop
@section('custom-js-section')
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
    <script>
        $(document).ready(function () {
            ClassicEditor.create(document.querySelector('#content_data'));
        });
    </script>
@stop
