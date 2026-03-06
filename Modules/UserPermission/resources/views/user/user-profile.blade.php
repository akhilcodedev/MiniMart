@extends('dashboard::includes.layouts.backend', ['title' => __('User Profile')])
@section('content')
@if (\Session::has('success'))
    <div class="alert alert-success">
        <span>{!! \Session::get('success') !!}</span>
    </div>
@endif

@if (\Session::has('error'))
    <div class="alert alert-danger">
        <span>{!! \Session::get('error') !!}</span>
    </div>
@endif
<div class="card profile-page">
    <div class="card-header border-0 pt-5">
        <h1 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold fs-1 mb-1">{{ __("messages.Profile") }}</span>
        </h1>
    </div>
    <div class="card-body py-3">
        @if(Auth::user()->is_superadmin == 1 || auth()->user()->can('setting-profile-view'))
            <form id="UpdateClient" method="post" action="{{route('user.profile.update')}}" enctype="multipart/form-data"
                class="from row">
                <div class="mb-7 col-md-12">
                    <div class="card-header py-0 mx-n9">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">{{ __("messages.Organization details") }}</span>
                            <span class="text-muted mt-1 fw-semibold fs-7">
                                {{ __("messages.View and edit your organization's profile details.") }}
                            </span>
                        </h3>
                        <div class="card-toolbar">
                            <button type="submit" class=" btn btn-sm btn-light-primary">{{ __("messages.Update") }}</button>
                        </div>
                    </div>
                </div>

                @method('PUT')
                @csrf
                <div class="row">
                    <div class="col-md-12">
                        @if(isset($client_preference_detail->ios_link) && !empty($client_preference_detail->ios_link))
                            <div class="text-center mb-7 choose-btn">
                                <a href="{{ $client_preference_detail->ios_link }}" target="_blank" class="g-btn">
                                    <span class="icon alAppleIcon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g clip-path="url(#clip0_193_116)">
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M13.0724 1.85586C13.9617 0.814184 15.4496 0.0506692 16.69 0.000151195C16.7918 -0.00386192 16.8822 0.0724659 16.8936 0.175233C17.0374 1.48666 16.5619 2.95073 15.6215 4.09156C14.728 5.17195 13.4196 5.84293 12.2068 5.84293C12.1217 5.84285 12.0362 5.83955 11.9525 5.83302C11.8599 5.82586 11.785 5.7548 11.7728 5.66273C11.5782 4.19126 12.3166 2.73143 13.0724 1.85586ZM4.71012 20.9263C2.4664 17.6866 1.17654 12.3434 3.20552 8.82248C4.27254 6.96668 6.20796 5.79486 8.25622 5.76472C8.2766 5.76432 8.2973 5.76409 8.31831 5.76409C9.19923 5.76409 10.0314 6.09426 10.7655 6.38565L10.7661 6.3859C11.3149 6.6036 11.7888 6.7916 12.154 6.7916C12.4791 6.7916 12.9505 6.60574 13.4963 6.39053C14.287 6.07876 15.2711 5.69075 16.2985 5.69075C16.4301 5.69075 16.5611 5.69712 16.688 5.70979C17.5627 5.74741 19.7353 6.06004 21.1528 8.13442C21.1827 8.17817 21.1937 8.23231 21.1832 8.28432C21.1726 8.33642 21.1415 8.38198 21.0969 8.4107L21.0777 8.42266C20.6659 8.67745 18.6123 10.0832 18.6386 12.7379C18.667 16.0053 21.3694 17.2041 21.6774 17.3301L21.6918 17.3363C21.7832 17.3782 21.8282 17.4822 21.7963 17.5775L21.7895 17.5988C21.6222 18.135 21.1261 19.5381 20.1372 20.9832L20.1371 20.9835C19.1895 22.3675 18.1154 23.9362 16.3661 23.9688C15.5507 23.9841 14.9981 23.745 14.4622 23.5132L14.4577 23.5112L14.4574 23.5111C13.9123 23.2752 13.3486 23.0313 12.4654 23.0313C11.5369 23.0313 10.9449 23.2835 10.3726 23.5274L10.3719 23.5277C9.8623 23.7447 9.33514 23.9692 8.60576 23.9983C8.5757 23.9994 8.54635 24 8.51708 24C6.964 24 5.8301 22.5461 4.71012 20.9263Z"
                                                    fill="black" />
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_193_116">
                                                    <rect width="24" height="24" fill="white" />
                                                </clipPath>
                                            </defs>
                                        </svg></span>
                                    <span class="text"> Available on <strong>App Store</strong></span>
                                    <!-- <img class="w-100" src="{{asset('assets/images/iosstore.png')}}" alt="image" > -->
                                </a>
                            </div>
                        @endif

                        @if(isset($client_preference_detail->android_app_link) && !empty($client_preference_detail->android_app_link))
                            <div class="text-center mb-7 choose-btn">
                                <a href="{{ $client_preference_detail->android_app_link }}" target="_blank" class="g-btn">
                                    <span class="icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M12.7496 10.875L15.8996 7.5L3.67461 0.9C3.59961 0.825 3.44961 0.825 3.22461 0.75L12.7496 10.875Z"
                                                fill="#00F076" />
                                            <path
                                                d="M17.2496 15.75L21.6746 13.35C22.1996 13.05 22.4996 12.6 22.4996 12C22.4996 11.4 22.1996 10.875 21.6746 10.65L17.2496 8.25L13.7246 12L17.2496 15.75Z"
                                                fill="#FFC900" />
                                            <path
                                                d="M1.8 1.42499C1.575 1.64999 1.5 1.94999 1.5 2.24999V21.75C1.5 22.05 1.575 22.35 1.8 22.65L11.7 12L1.8 1.42499Z"
                                                fill="#00D6FF" />
                                            <path
                                                d="M12.7496 13.125L3.22461 23.25C3.37461 23.25 3.52461 23.175 3.67461 23.1L15.8996 16.5L12.7496 13.125Z"
                                                fill="#FF3A44" />
                                        </svg></span>
                                    <span class="text"> Available on <strong>Google Play</strong></span>
                                    <!-- <img class="w-100" src="{{asset('assets/images/playstore.png')}}" alt="image" > -->
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <div class=" row">

                    <div class="col-md-4 mb-7">
                        <label for="name" class="form-label fw-semibold">{{ __("messages.NAME") }}</label>
                        <input type="text" class="form-control form-control-solid al_box_height" name="name" id="name"
                            value="{{ old('name', Auth::user()->name ?? '')}}" placeholder="John Doe">
                        @if($errors->has('name'))
                            <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('name') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="col-md-4 mb-7">
                        <label for="email" class="form-label fw-semibold">{{ __("messages.EMAIL") }}</label>
                        <input type="email" class="form-control form-control-solid al_box_height" id="email" name="email"
                            value="{{ old('email', Auth::user()->email ?? '')}}" placeholder="Enter email address"
                            disabled="" style="cursor:not-allowed;">
                        @if($errors->has('email'))
                            <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="col-md-4 mb-7">
                        <label for="phone_number" class="form-label fw-semibold">{{ __("messages.CONTACT NUMBER") }}</label>
                        <div class="input-group">
                            <input type="text" class="form-control form-control-solid al_box_height" name="phone_number"
                                id="phone_number" value="{{ old('phone_number', Auth::user()->phone_number ?? '')}}">
                        </div>
                        @if($errors->has('phone_number'))
                            <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('phone_number') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="col-md-4 mb-7" id="countryInput">
                        <label class="form-label fw-semibold" for="country">{{ __("messages.COUNTRY") }}</label>
                        @if($errors->has('country'))
                            <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('country') }}</strong>
                            </span>
                        @endif
                        <select class="form-select form-select-solid al_box_height" data-control="select2" id="country"
                            name="country_id" value="{{ old('country', $client->id ?? '')}}" placeholder="Country">
                            @foreach($countries as $code => $country)
                                <option value="{{ $country->id }}" @if($client->country_id == $country->id) selected @endif>
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback" role="alert">
                            <strong></strong>
                        </span>
                    </div>

                    <div class="col-md-4 mb-7" id="timezoneInput">
                        <label class="form-label fw-semibold" for="timezone">{{ __("messages.TIMEZONE") }}</label>
                        @if($errors->has('timezone'))
                            <span class="text-danger" role="alert">
                                <strong>{{ $errors->first('timezone') }}</strong>
                            </span>
                        @endif
                        <select class="form-select form-select-solid al_box_height" id="timezone" name="timezone" data-control="select2"
                            value="{{ old('timezone', $client->timezone ?? '')}}" placeholder="Timezone">
                            @foreach($tzlist as $tz)
                                <option value="{{ $tz->timezone }}" @if($client->timezone == $tz->timezone) selected @endif>
                                    {{ $tz->timezone }} {{ $tz->diff_from_gtm }}
                                </option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback" role="alert">
                            <strong></strong>
                        </span>
                    </div>

                    <div class="col-md-4 mb-7">
                        <label for="image" class="form-label fw-semibold"><?php echo e(__("messages.IMAGE")); ?></label>
                        <input type="file" class="form-control form-control-solid al_box_height" name="image" id="image" placeholder="upload you profile">
                        <?php if(Auth::user()->image): ?>
                        <img src="<?php echo e(asset('public/images/vendor-dashboard/profile/' . Auth::user()->image)); ?>" alt="User Image" style="width: 100px; height: auto;">
                        <?php endif; ?>
                        <?php if($errors->has('image')): ?>
                        <span class="text-danger" role="alert">
                        <strong><?php echo e($errors->first('image')); ?></strong>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        @endif


    </div>



</div> <!-- container -->
@endsection

@section('custom-js-section')
<script src="{{asset('assets/libs/dropzone/dropzone.min.js')}}"></script>
<script src="{{asset('assets/libs/dropify/dropify.min.js')}}"></script>
<!-- Page js-->
<script src="{{asset('assets/js/pages/form-fileuploads.init.js')}}"></script>
<script src="{{asset('assets/js/storeClients.js')}}"></script>


<script>

    // var regEx = /\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/;
    // $("#UpdateClient").bind("submit", function() {
    //     var val = $("#phone_number").val();
    //     if (!val.match(regEx)) {
    //         $('#phone_number').css('color', 'red');
    //         return false;
    //     }
    // });

    $(function () {
        $('#phone_number').focus(function () {
            $('#phone_number').css('color', '#6c757d');
        });
    });
</script>
@endsection
