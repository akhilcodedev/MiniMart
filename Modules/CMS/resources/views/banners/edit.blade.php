@extends('base::layouts.mt-main')

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bolder fs-3 mb-1">{{ __('Edit Hero Banner') }}</span>
            </h3>
            <div class="card-toolbar">
                <a href="{{ route('banners.index') }}" class="btn btn-sm btn-light">
                    <i class="fas fa-arrow-left"></i>{{ __('Back to List') }}
                </a>
            </div>
        </div>
        <div class="card-body py-3">
            @if ($errors->any())
                <div class="alert alert-danger d-flex align-items-center p-5 mb-10">
                    <i class="fas fa-exclamation-triangle me-3 fs-2x"></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1">{{ __('Error') }}</h4>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('banners.update', $hero_banner->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-5">
                    <label for="title" class="form-label">{{ __('Title') }}</label>
                    <input type="text" name="title" id="title" class="form-control form-control-solid" value="{{ old('title', $hero_banner->title) }}" required>
                </div>
                <div class="mb-5">
                    <label for="subtitle" class="form-label">{{ __('Subtitle') }}</label>
                    <input type="text" name="subtitle" id="subtitle" class="form-control form-control-solid" value="{{ old('subtitle', $hero_banner->subtitle) }}">
                </div>
                <div class="mb-5">
                    <label for="description" class="form-label">{{ __('Description') }}</label>
                    <textarea name="description" id="description" class="form-control form-control-solid" rows="7">{{ old('description', $hero_banner->description) }}</textarea>
                </div>

                <div class="mb-5">
                    <label for="banner_images" class="form-label">{{ __('Banner Images') }}</label>
                    <input type="file" name="banner_images" id="banner_images" class="form-control form-control-solid">
                     @if($hero_banner->images && count($hero_banner->images) > 0)
                        <div class="row mt-2">
                           <div class="mb-5">
                           <img src="{{asset('storage/'.$hero_banner->images[0])}}" alt="" width="200px">
                           </div>
                         </div>
                      @endif
                </div>
                 <div class="mb-5">
                    <label for="alt_tag" class="form-label">{{ __('Alt Tag') }}</label>
                   <input type="text" name="alt_tag" id="alt_tag" class="form-control form-control-solid" value="{{ old('alt_tag', $hero_banner->alt_tag) }}">
               </div>
                <div class="mb-5">
                   <label for="position" class="form-label">{{ __('Position') }}</label>
                   <input type="number" name="position" id="position" class="form-control form-control-solid" value="{{ old('position', $hero_banner->position) }}">
                 </div>
                 <div class="mb-5">
                    <div class="form-check form-switch d-flex align-items-center">
                         <input class="form-check-input me-3" type="checkbox" name="status" id="status" {{ $hero_banner->status ? 'checked' : '' }} value="1">
                         <label class="form-check-label" for="status">{{ __('Status') }}</label>
                    </div>
                </div>                     
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sync"></i>{{ __('Update') }}
                </button>
            </form>
        </div>
    </div>
@endsection

@section('custom-js-section')
<script>
$(document).ready(function () {
    $('.delete-image').on('click', function (e) {
        e.preventDefault();

        const button = $(this);
        const imageUrl = button.data('url');
        const bannerId = button.data('banner-id');
        const imageContainer = button.closest('.col-md-3');

        Swal.fire({
            title: '{{ __('Are you sure?') }}',
            text: "{{ __('You won\'t be able to revert this!') }}",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '{{ __('Yes, delete it!') }}'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route('banners.delete-banner-image') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        url: imageUrl,
                        banner_id: bannerId
                    },
                    success: function(response) {
                        console.log('Server Response:', response);
                        if (response.success) {
                            imageContainer.remove();
                            Swal.fire('{{ __('Deleted!') }}', response.message, 'success');
                        } else {
                            Swal.fire('{{ __('Error!') }}', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr.responseText);
                        Swal.fire('{{ __('Error!') }}', '{{ __('Something went wrong while deleting the image.') }}', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endsection