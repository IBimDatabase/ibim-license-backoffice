@extends('layouts.main')
@section('content')
<div class="container-fluid py-4" ng-controller="userController">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">All Users</h5>
                        </div>
                        <a href="javascript:void(0)" class="btn bg-gradient-primary mb-0" type="button" data-bs-toggle="modal" data-bs-target="#addUserModal" ng-click="clearFormData('addUserForm')"><i class="fa fa-plus-circle"></i> Add User </a>
                    </div>
                </div>
                <!--pt-5 Removed By [AR-07-Dev2025]-->
                <div class="card-body px-0 pb-2">
                    <!-- loader -->
                    <div class="loader-overlay" ng-show="loading">
                        <div class="loader-gif"></div>
                    </div>

                    <div class="px-4 py-2 bg-light-gray d-flex flex-row justify-content-between">
                        <div class="pt-2 hide-on-load" ng-class="{show: totalData}">
                            <span class="text-brown">Showing</span> 
                            <span class="font-weight-bolder text-dark" ng-bind="dataFrom ? dataFrom : 0"></span>
                            <span class="text-brown">to</span> 
                            <!--<span class="font-weight-bolder text-dark" ng-bind="dataTo ? dataTo : 0"></span>-->
                            <input type="number" class="form-control" style="width:70px; display:inline-block" ng-model="perPage" ng-change="changePerPage()" min="1" placeholder="Rows">

                            <span class="text-brown">of</span> 
                            <span class="font-weight-bolder text-dark" ng-bind="totalData"></span>
                            <span class="text-brown">Results</span> 
                        </div>
                        <div class="pt-2 font-weight-bolder text-dark hide-on-load" ng-class="{show: !totalData}" ng-bind="'No Results'"></div>

                        <a href="javascript:void(0)" class="btn bg-info mb-0 filter-toggle-btn text-white" type="button" ng-click="togglefilters()" ng-class="{show: !filtersToggle}" ng-bind="'Show Filters'"></a>
                        <a href="javascript:void(0)" class="btn bg-warning mb-0 filter-toggle-btn text-white" type="button" ng-click="togglefilters()" ng-class="{show: filtersToggle}" ng-bind="'Hide Filters'"></a>
                    </div>

                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-info text-xs font-weight-bolder">
                                        Sno
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder" ng-click="sortByField('first_name')">
                                        Full Name <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder" ng-click="sortByField('user_name')">
                                        User Name <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder" ng-click="sortByField('email')">
                                        Email <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder" ng-click="sortByField('phone')">
                                        Phone <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder" ng-click="sortByField('user_type')">
                                        User Type <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder" ng-click="sortByField('status')">
                                        Status <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder" ng-click="sortByField('created_at')">
                                        Created On <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody>

                                <tr class="filter-row" ng-class="{show: filtersToggle}">
                                    <td></td>
                                    <td>
                                        <input type="text" class="form-control" ng-model="nameFilter" ng-change="callPaginateData()" placeholder="Search Here...">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" ng-model="userNameFilter" ng-change="callPaginateData()" placeholder="Search Here...">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" ng-model="emailFilter" ng-change="callPaginateData()" placeholder="Search Here...">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" ng-model="phoneFilter" ng-change="callPaginateData()" placeholder="Search Here...">
                                    </td>
                                    <td>
                                        <select class="form-control" ng-model="userTypeFilter" ng-change="getPaginateData()">
                                            <option value=""> -- Select -- </option>
                                            <option ng-repeat="userRole in userRoles" value="[[ userRole.role_code ]]"> [[ userRole.role_name ]] </option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control" ng-model="statusFilter" ng-change="getPaginateData()">
                                            <option value=""> -- Status -- </option>
                                            <option value="ACTIVE"> Active </option>
                                            <option value="INACTIVE"> Inactive </option>
                                        </select>
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>

                                <tr class="data-row" ng-repeat="user in users | orderBy: sortBy : reverse" ng-class="{show: users}">
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="($index + dataFrom)"></p>
                                    </td>
                                    
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="(user.last_name) ? user.first_name + ' ' + user.last_name : user.first_name"></p>
                                    </td>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="user.user_name"></p>
                                    </td>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="user.email"></p>
                                    </td>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="user.phone"></p>
                                    </td>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="user.user_type | textCapitalize"></p>
                                    </td>
                                    <td class="ps-4">
                                        <p class="text-sm font-weight-bold text-capitalize mb-0">
                                            <span class="badge bg-gradient-success status-badge-one" ng-if="user.status == 'ACTIVE'" ng-bind="user.status | textCapitalize"></span>
                                            <span class="badge bg-gradient-danger status-badge-one" ng-if="user.status == 'INACTIVE'" ng-bind="user.status | textCapitalize"></span>
                                        </p>
                                    </td>
                                    <td class="ps-4 text-nowrap">
                                        <span class="text-secondary text-xs font-weight-bold" ng-bind="user.created_at | strToDate | date : 'dd-MM-y hh:mm:ss a'"></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="javascript:void(0);" class="mx-1" data-bs-toggle="modal" data-bs-target="#updateModal" ng-click="getUser(user)">
                                            <i class="fas fa-edit text-info"></i>
                                        </a>
                                    </td>
                                </tr>

                                <tr class="no-data-found-row" ng-class="{show: users.length == 0}">
                                    <td colspan="8" class="text-center text-secondary">No Data Found.</td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="mt-5 pagination-container" ng-class="{show: users.length > 0}">
                            <pagination></pagination>
                        </div>  
                        
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Add License Type Modal -->
    <div class="modal fade" id="addUserModal" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white" id="addUserModalLabel">Add User</h5>
                    <button type="button" class="btn-close"data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    
                    <form name="addUserForm" method="post" ng-submit="addUser()">
                        @csrf
                        <div class="row">

                            <div class="mb-4">
                                <h5 class="text-brown"> Basic Details </h5>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="firstName"> First Name <span class="text-warning">*</span> </label>
                                <input id="firstName" type="text" name="firstName" class="form-control"
                                    placeholder="First Name" ng-model="firstName" required>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="addUserForm.firstName.$touched && addUserForm.firstName.$invalid">
                                    <span ng-show="addUserForm.firstName.$error.required">* The first name field is required.</span>
                                </div>
                            </div> 

                            <div class="col-lg-6 mb-3">
                                <label for="lastName"> Last Name </label>
                                <input id="lastName" type="text" name="lastName" class="form-control"
                                    placeholder="Last Name" ng-model="lastName">
                            </div> 

                            <div class="col-lg-6 mb-3">
                                <label for="email"> Email <span class="text-warning">*</span> </label>
                                <input id="email" type="email" name="email" class="form-control"
                                    placeholder="Email" ng-model="email" required>

                                <div class="text-danger mt-1 text-xs" ng-show="addUserForm.email.$touched && addUserForm.email.$invalid"> 
                                    <span ng-show="addUserForm.email.$error.required">* The email field is required.</span>
                                    <span ng-show="addUserForm.email.$error.email">* Please enter a valid email address.</span>
                                </div>
                            </div> 

                            <div class="col-lg-6 mb-3">
                                <label for="phone"> Phone No. <span class="text-warning">*</span> </label>
                                <input id="phone" type="text" name="phone" class="form-control"
                                    placeholder="Phone No." maxlength="30" ng-model="phone" required>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="addUserForm.phone.$touched && addUserForm.phone.$invalid">
                                    <span ng-show="addUserForm.phone.$error.required">* The phone no. field is required.</span>
                                </div>
                            </div>


                            <div class="mt-4">
                                <h5 class="mb-4 text-brown"> Account Details </h5>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="userName"> User Name <span class="text-warning">*</span> </label>
                                <input id="userName" type="text" name="userName" class="form-control"
                                    placeholder="User Name" ng-model="userName" required>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="addUserForm.userName.$touched && addUserForm.userName.$invalid">
                                    <span ng-show="addUserForm.userName.$error.required">* The user name field is required.</span>
                                </div>
                            </div> 

                            <div class="col-lg-6 mb-3">
                                <label for="userType"> User Type <span class="text-warning">*</span> </label>
                                <select id="userType" name="userType" class="form-control" ng-model="userType" required>
                                    <option value=""> -- User Type -- </option>
                                    <option ng-repeat="userRole in userRoles" value="[[ userRole.role_code ]]"> [[ userRole.role_name ]] </option>
                                </select>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="addUserForm.userType.$touched && addUserForm.userType.$invalid">
                                    <span ng-show="addUserForm.userType.$error.required">* The user type is required.</span>
                                </div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="password"> Password <span class="text-warning">*</span> </label>
                                <div class="input-group">
                                    <input id="password" type="[[ (passwordToggle == true) ? 'password' : 'text' ]]" name="password" class="form-control"
                                    placeholder="Password" ng-model="password" aria-describedby="input-addon" required>
                                    <span class="input-group-text" id="input-addon"><i class="fa fa-eye cursor-pointer" ng-click="togglePassword()"></i></span>
                                
                                    <div class="text-danger mt-1 text-xs" ng-show="addUserForm.password.$touched && addUserForm.password.$invalid">
                                        <span ng-show="addUserForm.password.$error.required">* The password field is required.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="confirmPassword"> Confirm Password <span class="text-warning">*</span> </label>
                                <div class="input-group">
                                    <input id="confirmPassword" type="[[ (cnfmPasswordToggle == true) ? 'password' : 'text' ]]" name="confirmPassword" class="form-control"
                                    placeholder="Confirm Password" ng-model="confirmPassword" required>
                                    <span class="input-group-text" id="input-addon"><i class="fa fa-eye cursor-pointer" ng-click="toggleCnfmPassword()"></i></span>
                                
                                    <div class="text-danger mt-1 text-xs" ng-show="addUserForm.confirmPassword.$touched && addUserForm.confirmPassword.$invalid">
                                        <span ng-show="addUserForm.confirmPassword.$error.required">* The confirm password field is required.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="status"> Status <span class="text-warning">*</span> </label>
                                <select id="status" name="status" class="form-control" ng-model="status" required>
                                    <option value=""> -- Status -- </option>
                                    <option value="ACTIVE"> Active </option>
                                    <option value="INACTIVE"> Inactive </option>
                                </select>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="addUserForm.status.$touched && addUserForm.status.$invalid">
                                    <span ng-show="addUserForm.status.$error.required">* The status field is required.</span>
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

                            <div class="text-danger mt-4 mb-2" id="addUser-error-res"></div>

                            <div class="col-md-12 mt-4 pt-4 border-top text-right">
                                <button type="submit" class="btn bg-gradient-warning"> Submit </button>
                            </div>
                        </div>
                    </form>
                </div>
             </div>
        </div>
    </div>


    <!-- Update Modal -->
    <div class="modal fade" id="updateModal" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white" id="updateModalLabel">Update User</h5>
                    <button type="button" class="btn-close"data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    
                    <form name="updateUserForm" method="post" ng-submit="updateUser(userId)">
                        @csrf
                        <div class="row">

                            <div class="mb-4">
                                <h5 class="text-brown"> Basic Details </h5>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="updateFirstName"> First Name <span class="text-warning">*</span> </label>
                                <input id="updateFirstName" type="text" name="updateFirstName" class="form-control"
                                    placeholder="First Name" ng-model="updateFirstName" required>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="updateUserForm.updateFirstName.$touched && updateUserForm.updateFirstName.$invalid">
                                    <span ng-show="updateUserForm.updateFirstName.$error.required">* The first name field is required.</span>
                                </div>
                            </div> 

                            <div class="col-lg-6 mb-3">
                                <label for="updateLastName"> Last Name </label>
                                <input id="updateLastName" type="text" name="updateLastName" class="form-control"
                                    placeholder="Last Name" ng-model="updateLastName">
                            </div> 

                            <div class="col-lg-6 mb-3">
                                <label for="updateEmail"> Email <span class="text-warning">*</span> </label>
                                <input id="updateEmail" type="email" name="updateEmail" class="form-control"
                                    placeholder="Email" ng-model="updateEmail" required>

                                <div class="text-danger mt-1 text-xs" ng-show="updateUserForm.updateEmail.$touched && updateUserForm.updateEmail.$invalid"> 
                                    <span ng-show="updateUserForm.updateEmail.$error.required">* The email field is required.</span>
                                    <span ng-show="updateUserForm.updateEmail.$error.email">* Please enter a valid email address.</span>
                                </div>
                            </div> 

                            <div class="col-lg-6 mb-3">
                                <label for="updatePhone"> Phone No. <span class="text-warning">*</span> </label>
                                <input id="updatePhone" type="text" name="updatePhone" class="form-control"
                                    placeholder="Phone No." maxlength="30" ng-model="updatePhone" required>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="updateUserForm.updatePhone.$touched && updateUserForm.updatePhone.$invalid">
                                    <span ng-show="updateUserForm.updatePhone.$error.required">* The phone no. field is required.</span>
                                </div>
                            </div>


                            <div class="mt-4">
                                <h5 class="mb-4 text-brown"> Account Details </h5>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="updateUserName"> User Name <span class="text-warning">*</span> </label>
                                <input id="updateUserName" type="text" name="updateUserName" class="form-control"
                                    placeholder="User Name" ng-model="updateUserName" required>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="updateUserForm.updateUserName.$touched && updateUserForm.updateUserName.$invalid">
                                    <span ng-show="updateUserForm.updateUserName.$error.required">* The user name field is required.</span>
                                </div>
                            </div> 

                            <div class="col-lg-6 mb-3">
                                <label for="updateUserType"> User Type <span class="text-warning">*</span> </label>
                                <select id="updateUserType" name="updateUserType" class="form-control" ng-model="updateUserType" required>
                                    <option value=""> -- User Type -- </option>
                                    <option ng-repeat="userRole in userRoles" value="[[ userRole.role_code ]]"> [[ userRole.role_name ]] </option>
                                </select>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="updateUserForm.updateUserType.$touched && updateUserForm.updateUserType.$invalid">
                                    <span ng-show="updateUserForm.updateUserType.$error.required">* The user type is required.</span>
                                </div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="updateStatus"> Status <span class="text-warning">*</span> </label>
                                <select id="updateStatus" name="updateStatus" class="form-control" ng-model="updateStatus" required>
                                    <option value=""> -- Status -- </option>
                                    <option value="ACTIVE"> Active </option>
                                    <option value="INACTIVE"> Inactive </option>
                                </select>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="updateUserForm.updateStatus.$touched && updateUserForm.updateStatus.$invalid">
                                    <span ng-show="updateUserForm.updateStatus.$error.required">* The status field is required.</span>
                                </div>
                            </div> 
                                
                            <div class="col-lg-6 mb-3" style="display: [[ (authenticatedUser.user_type == 'SUPER_ADMIN') ? 'block' : 'none' ]]">
                                 <label for="updateStatus"> Password </label>
                                <button type="button" class="btn bg-info mb-0 filter-toggle-btn text-white ng-binding show" ng-click="showPasswordFields = !showPasswordFields">
                                    [[ showPasswordFields ? 'Hide Password Fields' : 'Change Password' ]]
                                </button>
                                <input type="hidden" name="change_password" value="[[ showPasswordFields ? 1 : 0 ]]">
                            </div>
                            <div class="row" ng-show="showPasswordFields">

                                <div class="col-lg-6 mb-3">
                                    <label for="password"> Password <span class="text-warning">*</span> </label>
                                    <div class="input-group">
                                        <input id="password"
                                            type="[[ (passwordToggle == true) ? 'password' : 'text' ]]"
                                            name="password"
                                            class="form-control"
                                            placeholder="Password"
                                            ng-model="password"
                                            aria-describedby="input-addon"
                                            ng-required="showPasswordFields">
                                        <span class="input-group-text">
                                            <i class="fa fa-eye cursor-pointer" ng-click="togglePassword()"></i>
                                        </span>

                                        <div class="text-danger mt-1 text-xs"
                                            ng-show="updateUserForm.password.$touched && updateUserForm.password.$invalid">
                                            <span ng-show="updateUserForm.password.$error.required">
                                                * The password field is required.
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <label for="confirmPassword"> Confirm Password <span class="text-warning">*</span> </label>
                                    <div class="input-group">
                                        <input id="confirmPassword"
                                            type="[[ (cnfmPasswordToggle == true) ? 'password' : 'text' ]]"
                                            name="confirmPassword"
                                            class="form-control"
                                            placeholder="Confirm Password"
                                            ng-model="confirmPassword"
                                            ng-required="showPasswordFields">
                                        <span class="input-group-text">
                                            <i class="fa fa-eye cursor-pointer" ng-click="toggleCnfmPassword()"></i>
                                        </span>

                                        <div class="text-danger mt-1 text-xs"
                                            ng-show="updateUserForm.confirm_password.$touched && updateUserForm.confirm_password.$invalid">
                                            <span ng-show="updateUserForm.confirm_password.$error.required">
                                                * The confirm password field is required.
                                            </span>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="text-danger mt-4 mb-2" id="updateUser-error-res"></div>

                            <div class="col-md-12 mt-4 pt-4 border-top text-right">
                                <button type="submit" class="btn bg-gradient-warning"> Submit </button>
                            </div>
                        </div>
                    </form>
                </div>
             </div>
        </div>
    </div>

</div>
@endsection
