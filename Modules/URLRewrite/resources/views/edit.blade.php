@extends('base::layouts.mt-main')

@section('content')

<div class="post d-flex flex-column-fluid" id="kt_post">
    <div id="kt_content_container" class="container-xxl">
        <div class="card card-flush">
            <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                <div class="card-title">
                    <h2>Edit URL Rewrite</h2>
                </div>
            </div>
            <div class="card-body pt-0">
                <form action="{{ route('urlrewrite.update', $urlRewrite->id) }}" method="POST">
                    @csrf
                    @method('PUT') 
                    <div class="mb-10">
                        <label for="entity_type" class="required form-label">Entity Type</label>
                        <input type="text" class="form-control form-control-solid" name="entity_type" id="entity_type"
                            placeholder="Entity Type" value="{{ $urlRewrite->entity_type }}" required />
                    </div>

                    <div class="mb-10">
                        <label for="entity_id" class="required form-label">Entity ID</label>
                        <input type="number" class="form-control form-control-solid" name="entity_id" id="entity_id"
                            placeholder="Entity ID" value="{{ $urlRewrite->entity_id }}" required />
                    </div>

                    <div class="mb-10">
                        <label for="request_path" class="required form-label">Request Path</label>
                        <input type="text" class="form-control form-control-solid" name="request_path" id="request_path"
                            placeholder="Request Path" value="{{ $urlRewrite->request_path }}" required />
                    </div>

                    <div class="mb-10">
                        <label for="target_path" class="required form-label">Target Path</label>
                        <input type="text" class="form-control form-control-solid" name="target_path" id="target_path"
                            placeholder="Target Path" value="{{ $urlRewrite->target_path }}" required />
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('urlrewrite.index') }}" class="btn btn-light me-5">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection