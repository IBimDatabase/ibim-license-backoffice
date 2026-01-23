<main class="main-content mt-1 border-radius-lg" ng-controller="authController">
    <!--(border-radius-xl): Removed_by_Abdul_Rehman_For_UI_Changes-->
    <style>
        #navbarBlur{
            background: #404E67;
            margin-right:0px !important;
        }
       .shadow-none .text-dark, .shadow-none .text-capitalize, .breadcrumb-item+.breadcrumb-item::before{
            color: white !important;
        }
        .bg-info{
            background-color:#404E67 !important;
        }
        .bg-gradient-info, .bg-gradient-primary{
            background: #edbd1c;
        }
        .bg-gradient-info:hover, .bg-gradient-primary:hover{
            background: #edbd1c !important;
        }

        .table-responsive .table>thead{
            background: #404e67 !important;
        }
        .table-responsive .table > thead > tr > th.text-info {
            color:white !important;
        }
    </style>
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none " id="navbarBlur"
        navbar-scroll="true">
        <div class="container-fluid py-1 px-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                    <li class="breadcrumb-item text-md"><a class="text-dark" href="javascript:;">Pages</a>
                    </li>
                    <li class="breadcrumb-item text-sm text-dark active text-capitalize" aria-current="page">
                        {{ str_replace('-', ' ', Route::currentRouteName()) }}</li>
                </ol>
                <h6 class="font-weight-bolder mb-0 text-capitalize">
                    {{ str_replace('-', ' ', Route::currentRouteName()) }}</h6>
            </nav>
            <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4 d-flex justify-content-end" id="navbar">
   
                <ul class="navbar-nav justify-content-end text-dark">
                    <li class="nav-item dropdown d-flex align-items-center">
                        <div class="dropdown-toggle cursor-pointer" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle text-info text-dark px-2"></i> <span class="font-weight-bold text-dark mt-1" ng-bind="(authenticatedUser.last_name) ? authenticatedUser.first_name + ' ' + authenticatedUser.last_name : authenticatedUser.first_name"></span>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end px-2 py-1 me-sm-n4">
                            <li class="ps-2">
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#changePasswordModal" ng-click="clearFormData('changePasswordForm');" class="nav-link font-weight-bold cursor-pointer px-0">
                                    <i class="fas fa-key"></i> Change Password
                                </a>
                            </li>

                            <li class="ps-2">
                                <a href="javascript:void(0)" ng-click="logout()" class="nav-link font-weight-bold cursor-pointer px-0">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                        
                    </li>
                    <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                        <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                            <div class="sidenav-toggler-inner">
                                <i class="sidenav-toggler-line"></i>
                                <i class="sidenav-toggler-line"></i>
                                <i class="sidenav-toggler-line"></i>
                            </div>
                        </a>
                    </li>
                    <!-- <li class="nav-item px-3 d-flex align-items-center">
                        <a href="javascript:;" class="nav-link text-body p-0">
                            <i class="fa fa-cog fixed-plugin-button-nav cursor-pointer"></i>
                        </a>
                    </li>
                    <li class="nav-item dropdown pe-2 d-flex align-items-center">
                        <a href="javascript:;" class="nav-link text-body p-0" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-bell cursor-pointer"></i>
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end  px-2 py-3 me-sm-n4" aria-labelledby="dropdownMenuButton">
                            <li class="mb-2">
                                <a class="dropdown-item border-radius-md" href="javascript:;">
                                    <div class="d-flex py-1">
                                        <div class="my-auto">
                                            <img src="../assets/img/team-2.jpg" class="avatar avatar-sm  me-3 ">
                                        </div>
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="text-sm font-weight-normal mb-1">
                                                <span class="font-weight-bold">New message</span> from Laur
                                            </h6>
                                            <p class="text-xs text-secondary mb-0">
                                                <i class="fa fa-clock me-1"></i>
                                                13 minutes ago
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li class="mb-2">
                                <a class="dropdown-item border-radius-md" href="javascript:;">
                                    <div class="d-flex py-1">
                                        <div class="my-auto">
                                            <img src="../assets/img/small-logos/logo-spotify.svg"
                                                class="avatar avatar-sm bg-gradient-dark  me-3 ">
                                        </div>
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="text-sm font-weight-normal mb-1">
                                                <span class="font-weight-bold">New album</span> by Travis Scott
                                            </h6>
                                            <p class="text-xs text-secondary mb-0">
                                                <i class="fa fa-clock me-1"></i>
                                                1 day
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item border-radius-md" href="javascript:;">
                                    <div class="d-flex py-1">
                                        <div class="avatar avatar-sm bg-gradient-secondary  me-3  my-auto">
                                            <svg width="12px" height="12px" viewBox="0 0 43 36" version="1.1"
                                                xmlns="http://www.w3.org/2000/svg"
                                                xmlns:xlink="http://www.w3.org/1999/xlink">
                                                <title>credit-card</title>
                                                <g id="Basic-Elements" stroke="none" stroke-width="1" fill="none"
                                                    fill-rule="evenodd">
                                                    <g id="Rounded-Icons"
                                                        transform="translate(-2169.000000, -745.000000)" fill="#FFFFFF"
                                                        fill-rule="nonzero">
                                                        <g id="Icons-with-opacity"
                                                            transform="translate(1716.000000, 291.000000)">
                                                            <g id="credit-card"
                                                                transform="translate(453.000000, 454.000000)">
                                                                <path class="color-background"
                                                                    d="M43,10.7482083 L43,3.58333333 C43,1.60354167 41.3964583,0 39.4166667,0 L3.58333333,0 C1.60354167,0 0,1.60354167 0,3.58333333 L0,10.7482083 L43,10.7482083 Z"
                                                                    id="Path" opacity="0.593633743"></path>
                                                                <path class="color-background"
                                                                    d="M0,16.125 L0,32.25 C0,34.2297917 1.60354167,35.8333333 3.58333333,35.8333333 L39.4166667,35.8333333 C41.3964583,35.8333333 43,34.2297917 43,32.25 L43,16.125 L0,16.125 Z M19.7083333,26.875 L7.16666667,26.875 L7.16666667,23.2916667 L19.7083333,23.2916667 L19.7083333,26.875 Z M35.8333333,26.875 L28.6666667,26.875 L28.6666667,23.2916667 L35.8333333,23.2916667 L35.8333333,26.875 Z"
                                                                    id="Shape"></path>
                                                            </g>
                                                        </g>
                                                    </g>
                                                </g>
                                            </svg>
                                        </div>
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="text-sm font-weight-normal mb-1">
                                                Payment successfully completed
                                            </h6>
                                            <p class="text-xs text-secondary mb-0">
                                                <i class="fa fa-clock me-1"></i>
                                                2 days
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </li> -->
                </ul>

            </div>
        </div>
    </nav>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white" id="changePasswordModalLabel">Change Password</h5>
                    <button type="button" class="btn-close"data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    
                    <form name="changePasswordForm" method="post" ng-submit="changePassword()">
                        @csrf
                        <div class="text-danger mb-2" id="changePassword-error-res"></div>
                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <label for="oldPassword"> Old Password <span class="text-warning">*</span> </label>
                                <div class="input-group">
                                    <input id="oldPassword" type="[[ (oldPasswordToggle == true) ? 'password' : 'text' ]]" name="oldPassword" class="form-control"
                                    placeholder="Password" ng-model="oldPassword" aria-describedby="input-addon" required>
                                    <span class="input-group-text" id="input-addon"><i class="fa fa-eye cursor-pointer" ng-click="toggleOldPassword()"></i></span>
                                
                                    <div class="text-danger mt-1 text-xs" ng-show="changePasswordForm.oldPassword.$touched && changePasswordForm.oldPassword.$invalid">
                                        <span ng-show="changePasswordForm.oldPassword.$error.required">* The old password field is required.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="password"> New Password <span class="text-warning">*</span> </label>
                                <div class="input-group">
                                    <input id="password" type="[[ (passwordToggle == true) ? 'password' : 'text' ]]" name="password" class="form-control"
                                    placeholder="Password" ng-model="password" aria-describedby="input-addon" required>
                                    <span class="input-group-text" id="input-addon"><i class="fa fa-eye cursor-pointer" ng-click="togglePassword()"></i></span>
                                
                                    <div class="text-danger mt-1 text-xs" ng-show="changePasswordForm.password.$touched && changePasswordForm.password.$invalid">
                                        <span ng-show="changePasswordForm.password.$error.required">* The new password field is required.</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-6 mb-3">
                                <label for="confirmPassword"> Confirm Password <span class="text-warning">*</span> </label>
                                <div class="input-group">
                                    <input id="confirmPassword" type="[[ (cnfmPasswordToggle == true) ? 'password' : 'text' ]]" name="confirmPassword" class="form-control"
                                    placeholder="Password" ng-model="confirmPassword" aria-describedby="input-addon" required>
                                    <span class="input-group-text" id="input-addon"><i class="fa fa-eye cursor-pointer" ng-click="toggleCnfmPassword()"></i></span>
                                
                                    <div class="text-danger mt-1 text-xs" ng-show="changePasswordForm.confirmPassword.$touched && changePasswordForm.confirmPassword.$invalid">
                                        <span ng-show="changePasswordForm.confirmPassword.$error.required">* The confirm password field is required.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="text-sm mt-2">
                                <span class="text-dark font-weight-bolder"> Note: </span> 
                                <span class="text-warning ms-2"> Password must contain - </span>
                                <div class="text-warning ms-5"> * At least one number </div>
                                <div class="text-warning ms-5"> * At least one uppercase letter </div>
                                <div class="text-warning ms-5"> * At least one lowercase letter </div>
                                <div class="text-warning ms-5"> * Minimum 8 letters </div> 
                            </div>

                            <div class="col-md-12 mt-4 pt-4 border-top text-right">
                                <button type="submit" class="btn bg-gradient-warning"> Submit </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>