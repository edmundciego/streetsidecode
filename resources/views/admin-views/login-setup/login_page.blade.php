@extends('layouts.admin.app')

@section('title',translate('login_page_setup'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header d-flex flex-wrap align-items-center justify-content-between">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/app.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{translate('login_setup')}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->

        <ul class="nav nav-tabs border-0 nav--tabs nav--pills mb-4">
            <li class="nav-item">
                <a class="nav-link active" href="">{{translate('Customer_Login')}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="">{{translate('login_page_Url')}}</a>
            </li>
        </ul>

        <div class="card">
            <div class="card-header">
                <div>
                    <h4 class="mb-1">
                        {{translate('Setup Login Option')}}
                    </h4>
                    <p class="fs-12 m-0">
                        {{translate('The option you select customer will have the to option to login')}}
                    </p>
                </div>
            </div>
            <div class="card-body pt-3">
                
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6 col-md-4">
                            <label class="form-check form--check form--check--inline border rounded">
                                <span class="user-select-none form-check-label flex-grow-1">
                                    {{translate('Manual Login')}}
                                    <span data-toggle="tooltip" data-placement="top" title="" data-original-title="Info">
                                        <i class="tio-info-outined"></i>
                                    </span>
                                </span>
                                <input class="form-check-input login-option-type" type="checkbox" name="manual_login" id="customer-manual-login" value="1" checked="">
                            </label>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <label class="form-check form--check form--check--inline border rounded">
                                <span class="user-select-none form-check-label flex-grow-1">
                                    {{translate('OTP Login')}}
                                    <span data-toggle="tooltip" data-placement="top" title="" data-original-title="Info">
                                        <i class="tio-info-outined"></i>
                                    </span>
                                </span>
                                <input class="form-check-input login-option-type" type="checkbox" name="otp_login" id="customer-otp-login" value="1">
                            </label>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <label class="form-check form--check form--check--inline border rounded">
                                <span class="user-select-none form-check-label flex-grow-1">
                                    {{translate('Social Media Login')}}
                                    <span data-toggle="tooltip" data-placement="top" title="" data-original-title="Info">
                                        <i class="tio-info-outined"></i>
                                    </span>
                                </span>
                                <input class="form-check-input login-option-type" type="checkbox" name="social_login" id="customer-social-login" value="1" checked="">
                            </label>
                        </div>
                    </div>

                    <div class="mb-4 social-media-login-container ">
                        <div class="mb-3">
                            <h4 class="mb-1">
                                {{translate('Social media login setup')}}
                            </h4>
                            <a href="" class="fs-12 c1 text-underline fw-semibold" target="_blank">
                                {{translate('Connect 3rd party login system from here')}}
                            </a>
                        </div>
                        <div class="bg-light p-4 rounded">
                            <h4 class="mb-1">
                                {{translate('Choose social media')}}
                            </h4>
                            <div class="row g-3">
                                <div class="col-sm-6 col-md-4">
                                    <label class="form-check form--check form--check--inline border rounded">
                                        <span class="user-select-none form-check-label flex-grow-1">
                                            {{translate('Google')}}
                                            <span data-toggle="tooltip" data-placement="top" title="" data-original-title="Info">
                                                <i class="tio-info-outined"></i>
                                            </span>
                                        </span>
                                        <input type="checkbox" name="google_login" id="google_login" value="1" class="form-check-input social-media-status-checkbox">
                                    </label>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <label class="form-check form--check form--check--inline border rounded">
                                        <span class="user-select-none form-check-label flex-grow-1">
                                            {{translate('Facebook')}}
                                            <span data-toggle="tooltip" data-placement="top" title="" data-original-title="Info">
                                                <i class="tio-info-outined"></i>
                                            </span>
                                        </span>
                                        <input type="checkbox" name="facebook_login" id="facebook_login" value="1" class="form-check-input social-media-status-checkbox" checked="">
                                    </label>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <label class="form-check form--check form--check--inline border rounded">
                                        <span class="user-select-none form-check-label flex-grow-1">
                                            {{translate('Apple')}}
                                            <span data-toggle="tooltip" data-placement="top" title="" data-original-title="Info">
                                                <i class="tio-info-outined"></i>
                                            </span>
                                        </span>
                                        <input type="checkbox" name="apple_login" value="1" class="form-check-input social-media-status-checkbox">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="mb-3">
                            <h4 class="mb-1">
                                {{translate('OTP Verification')}}
                            </h4>
                            <p class="fs-12">
                                {{translate('The option you select will need to be verified by the customer')}}
                            </p>
                        </div>
                        <div class="bg-light p-4 rounded">
                            <div class="row g-3">
                                <div class="col-sm-6 col-md-4">
                                    <label class="form-check form--check form--check--inline border rounded">
                                        <span class="user-select-none form-check-label flex-grow-1">
                                            {{translate('Email Verification')}}
                                            <span data-toggle="tooltip" data-placement="top" title="" data-original-title="Info">
                                                <i class="tio-info-outined"></i>
                                            </span>
                                        </span>
                                        <input type="checkbox" name="email_verification" value="1" class="form-check-input social-media-status-checkbox">
                                    </label>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <label class="form-check form--check form--check--inline border rounded">
                                        <span class="user-select-none form-check-label flex-grow-1">
                                            {{translate('Phone Number Verification')}}
                                            <span data-toggle="tooltip" data-placement="top" title="" data-original-title="Info">
                                                <i class="tio-info-outined"></i>
                                            </span>
                                        </span>
                                        <input type="checkbox" name="phone_verification" id="phone_verification" value="1" class="form-check-input social-media-status-checkbox">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end">
                        <button type="reset" class="btn btn--reset">Reset</button>
                        <button type="submit" class="btn btn--primary">Submit</button>
                    </div>
                
            </div>
        </div>

    </div>

@endsection

@push('script_2')

@endpush
