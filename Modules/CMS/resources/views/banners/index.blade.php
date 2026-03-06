@extends('base::layouts.mt-main')

@section('content')

    {{-- Check if user can list banners or is super admin before showing anything --}}
    @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('list_banners')))

        <div class="card">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1">{{ __('Hero Banners') }}</span>
                </h3>
                {{-- Check if user can create banners or is super admin before showing the add button --}}
                @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('create_banners')))
                    <div class="card-toolbar">
                        <a href="{{ route('banners.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> {{ __('Add Hero Banner') }}
                        </a>
                    </div>
                @endif
            </div>
            <div class="card-body py-3">
                <div class="table-responsive">
                    <table id="kt_datatable_column_rendering" class="table table-row-bordered table-row-gray-300 gy-7">
                        <thead>
                        <tr class="fw-bold fs-6 text-gray-800">
                            <th>Title</th>
                            <th>Images</th>
                            <th>Description</th>
                            <th>Status</th>
                            {{-- Only show Actions header if user can edit OR delete OR is super admin --}}
                            @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('edit_banners') || auth()->user()->can('delete_banners')))
                                <th class="text-end">Actions</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($hero_banners as $hero_banner)
                            <tr>
                                <td>{{ $hero_banner->title }}</td>
                                <td>
                                    <div class="d-flex">
                                        @if(!empty($hero_banner->images))
                                            {{-- Decode JSON if images are stored as JSON string --}}
                                            @php
                                                $bannerImages = is_string($hero_banner->images) ? json_decode($hero_banner->images, true) : $hero_banner->images;
                                            @endphp
                                            {{-- Ensure it's an array after decoding/checking --}}
                                            @if(is_array($bannerImages))
                                                @foreach($bannerImages as $image)
                                                    <div class="me-2">
                                                        {{-- Check if $image is just the path or contains more data --}}
                                                        @php
                                                            $imagePath = is_array($image) ? ($image['path'] ?? null) : $image;
                                                        @endphp
                                                        @if($imagePath)
                                                            <img src="{{ asset('storage/' . $imagePath) }}" width="150"
                                                                 alt="{{ $hero_banner->alt_tag ?? 'Banner Image' }}" class="rounded">
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @else
                                                <span>{{ __('Invalid Image Format') }}</span>
                                            @endif
                                        @else
                                            <span>{{ __('No Images Available') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $hero_banner->description }}</td>
                                <td>
                                        <span class="badge {{ $hero_banner->status === 1 ? 'badge-light-success' : 'badge-light-danger' }}">
                                            {{ $hero_banner->status === 1 ? __('Active') : __('Inactive') }}
                                        </span>
                                </td>
                                {{-- Show Actions column content only if user can edit OR delete OR is super admin --}}
                                @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('edit_banners') || auth()->user()->can('delete_banners')))
                                    <td class="text-end">
                                        {{-- Edit button check --}}
                                        @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('edit_banners')))
                                            <a href="{{ route('banners.edit', $hero_banner) }}"
                                               class="btn btn-sm btn-light btn-active-light-primary me-2">
                                                <i class="fas fa-edit"></i> {{ __('Edit') }}
                                            </a>
                                        @endif

                                        {{-- Delete button check --}}
                                        @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('delete_banners')))
                                            <form action="{{ route('banners.destroy', $hero_banner) }}" method="POST"
                                                  style="display:inline;" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-light btn-active-light-danger delete-banner">
                                                    <i class="fas fa-trash"></i> {{ __('Delete') }}
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            {{-- Adjust colspan based on whether Actions column is visible --}}
                            @php
                                $colspan = (auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('edit_banners') || auth()->user()->can('delete_banners'))) ? 5 : 4;
                            @endphp
                            <tr>
                                <td colspan="{{ $colspan }}" class="text-center text-gray-600">{{ __('No hero banners found') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Flash message display (no permission check needed here, shown if session exists) --}}
        @if(session('success') || session('error'))
            <div class="flash-message" data-type="{{ session('success') ? 'success' : 'error' }}"
                 data-message="{{ session('success') ?? session('error') }}"></div>
        @endif

        {{-- If user cannot list banners and is not super admin, show an unauthorized message --}}
    @else
        <div class="card">
            <div class="card-body">
                <div class="alert alert-danger text-center">
                    {{ __('You do not have permission to view this page.') }}
                </div>
            </div>
        </div>
    @endif

@endsection

@section('custom-js-section')
    {{-- Include scripts only if the user has permission to see the delete button --}}
    @if(auth()->check() && (auth()->user()->is_super_admin == 1 || auth()->user()->can('delete_banners')))
        {{-- Ensure jQuery and SweetAlert2 are loaded by the main layout or include them here --}}
        {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
        {{-- <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}
        <script>
            // Ensure the script runs after the DOM is fully loaded
            $(document).ready(function () {
                // Use event delegation for dynamically added elements or just being safer
                $('body').on('click', '.delete-banner', function (e) {
                    e.preventDefault(); // Prevent default button behavior

                    const button = $(this); // The delete button that was clicked
                    const form = button.closest('form.delete-form'); // Find the closest form

                    if (form.length === 0) {
                        console.error('Delete form not found!');
                        return; // Exit if form not found
                    }

                    // Use SweetAlert for confirmation
                    Swal.fire({
                        title: '{{ __('Are you sure?') }}',
                        text: "{{ __('You wont be able to revert this!') }}",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: '{{ __('Yes, delete it!') }}',
                        cancelButtonText: '{{ __('Cancel') }}' // Optional: Translate cancel button text
                    }).then((result) => {
                        // If the user confirmed the action
                        if (result.isConfirmed) {
                            // Submit the form programmatically
                            form.submit();
                        }
                    });
                });

                // Script for flash messages using SweetAlert (optional, depends on your setup)
                const flashMessage = $('.flash-message');
                if (flashMessage.length) {
                    const type = flashMessage.data('type'); // 'success' or 'error'
                    const message = flashMessage.data('message');

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: type,
                        title: message,
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                }
            });
        </script>
    @endif
@endsection
