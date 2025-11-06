@extends('layouts.main')
@section('content')
    <div class="page-header section-height-75">
        <div class="container">
            <div class="row">
                <div class="col-xl-4 col-lg-5 col-md-6 d-flex flex-column mx-auto">
                    <div class="card card-plain mt-1">
                        <div class="card-header pb-0 text-left bg-transparent">
                            <img src="../assets/img/logos/logo.png" class="mb-4 mx-sm-5 mx-3" alt="logo">
                            <h3 class="font-weight-bolder text-info text-gradient">{{ __('Welcome back') }}</h3>
                        </div>
                        <div class="card-body" ng-controller="authController">
                            <form name="loginForm" method="POST" role="form text-left" ng-submit="authenticate()">
                                @csrf
                                <div class="mb-3">
                                    <div class="text-danger mb-2 text-sm" id="login-error-res"></div>
                                    <label for="userName">Username / Email</label>
                                    <div class="@error('userName')border border-danger rounded-3 @enderror">
                                        <input id="userName" type="text" name="userName" class="form-control"
                                            placeholder="Username / Email" ng-model="userName" required>
                                    </div>
                                    <div class="text-danger mt-1 text-sm" ng-show="loginForm.userName.$touched && loginForm.userName.$invalid"> 
                                        <span class="form-field-error" ng-class="{show: loginForm.userName.$error.required}">* The username field is required.</span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="password">{{ __('Password') }}</label>
                                    <div class="@error('password')border border-danger rounded-3 @enderror">
                                        <input id="password" type="password" name="password" class="form-control"
                                            placeholder="Password" ng-model="password" required>
                                    </div>
                                    <div class="text-danger mt-1 text-sm" ng-show="loginForm.password.$touched">
                                        <span class="form-field-error" ng-class="{show: loginForm.password.$error.required}">* The password field is required.</span>
                                    </div>
                                </div>

                                <!-- <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="rememberMe" name="rememberMe">
                                    <label class="form-check-label" for="rememberMe">{{ __('Remember me') }}</label>
                                </div> -->

                                <div class="text-center">
                                    <button type="submit"
                                        class="btn bg-gradient-info w-100 mt-4 mb-0">{{ __('Sign in') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="oblique position-absolute top-0 h-100 d-md-block d-none me-n8">
                        <div class="oblique-image bg-cover position-absolute fixed-top ms-auto h-100 z-index-0 ms-n6"
                            style="background-image:url('../assets/img/curved-images/curved6.jpg')"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="notification-alert alert alert-success alert-dismissible fade">
        <button type="button" class="btn-close" ng-click="closeAlert('Success')"></button>
        <div class="text-white alert-message"></div>
    </div>
    
    <div class="notification-alert alert alert-warning alert-dismissible fade">
        <button type="button" class="btn-close" ng-click="closeAlert('Failure')"></button>
        <div class="text-white alert-message"></div>
    </div>
@endsection
