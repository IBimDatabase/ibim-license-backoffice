@extends('layouts.main')
@section('content')
<div class="container-fluid py-4" ng-controller="roleController">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">All Roles</h5>
                        </div>
                        <a href="javascript:void(0)" class="btn bg-gradient-primary mb-0" type="button" data-bs-toggle="modal" data-bs-target="#addRoleModal"><i class="fa fa-plus-circle"></i> Add Role </a>
                    </div>
                </div>
                <div class="card-body px-0 pt-5 pb-2">
                    <!-- loader -->
                    <div class="loader-overlay" ng-show="loading">
                        <div class="loader-gif"></div>
                    </div>

                    <div class="px-4 py-2 bg-light-gray d-flex flex-row justify-content-between">
                        <div class="pt-2 hide-on-load" ng-class="{show: totalData}">
                            <span class="text-brown">Showing</span> 
                            <span class="font-weight-bolder text-dark" ng-bind="dataFrom ? dataFrom : 0"></span>
                            <span class="text-brown">to</span> 
                            <span class="font-weight-bolder text-dark" ng-bind="dataTo ? dataTo : 0"></span>
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
                                    <th class="text-uppercase text-info text-xs font-weight-bolder opacity-7">
                                        Sno
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByField('role_name')">
                                        Role Name <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByField('role_code')">
                                        Role Code <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByField('role_description')">
                                        Description <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByField('status')">
                                        Status <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByField('created_at')">
                                        Created On <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7">
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
                                        <input type="text" class="form-control" ng-model="codeFilter" ng-change="callPaginateData()" placeholder="Search Here...">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" ng-model="descriptionFilter" ng-change="callPaginateData()" placeholder="Search Here...">
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

                                <tr class="data-row" ng-repeat="role in roles | orderBy: sortBy : reverse" ng-class="{show: roles}">
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="($index + dataFrom)"></p>
                                    </td>
                                    
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="role.role_name"></p>
                                    </td>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="role.role_code"></p>
                                    </td>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="role.role_description"></p>
                                    </td>
                                    <td class="ps-4">
                                        <p class="text-sm font-weight-bold text-capitalize mb-0">
                                            <span class="badge bg-gradient-success" ng-if="role.status == 'ACTIVE'" ng-bind="role.status | textCapitalize"></span>
                                            <span class="badge bg-gradient-danger" ng-if="role.status == 'INACTIVE'" ng-bind="role.status | textCapitalize"></span>
                                        </p>
                                    </td>
                                    <td class="ps-4">
                                        <span class="text-secondary text-xs font-weight-bold" ng-bind="role.created_at | strToDate | date : 'dd-MM-y hh:mm:ss a'"></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="javascript:void(0);" class="mx-1" data-bs-toggle="modal" data-bs-target="#updateModal" ng-click="getRole(role)">
                                            <i class="fas fa-edit text-info"></i>
                                        </a>
                                    </td>
                                </tr>

                                <tr class="no-data-found-row" ng-class="{show: roles.length == 0}">
                                    <td colspan="7" class="text-center text-secondary">No Data Found.</td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="mt-5 pagination-container" ng-class="{show: roles.length > 0}">
                            <pagination></pagination>
                        </div>  
                        
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Add License Type Modal -->
    <div class="modal fade" id="addRoleModal" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white" id="addRoleModalLabel">Add Role</h5>
                    <button type="button" class="btn-close"data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    
                    <form name="addRoleForm" method="post" ng-submit="addRole()">
                        @csrf
                        <div class="text-danger mb-2" id="addRole-error-res"></div>
                        <div class="row">
                  
                            <div class="col-lg-6 mb-3">
                                <label for="roleName"> Role Name <span class="text-warning">*</span> </label>
                                <input id="roleName" type="text" name="roleName" class="form-control"
                                    placeholder="Role Name" ng-model="roleName" ng-blur="generateRoleCode(roleName)" required>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="addRoleForm.roleName.$touched && addRoleForm.roleName.$invalid">
                                    <span ng-show="addRoleForm.roleName.$error.required">* The role name field is required.</span>
                                </div>
                            </div> 

                            <div class="col-lg-6 mb-3">
                                <label for="roleCode"> Role Code <span class="text-warning">*</span> </label>
                                <input id="roleCode" type="text" name="roleCode" class="form-control"
                                    placeholder="Role Code" ng-model="roleCode" required>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="addRoleForm.roleCode.$touched && addRoleForm.roleCode.$invalid">
                                    <span ng-show="addRoleForm.roleCode.$error.required">* The role code field is required.</span>
                                </div>
                            </div> 

                            <div class="col-lg-6 mb-3">
                                <label for="description"> Description </label>   
                                <textarea id="description" name="description" class="form-control"
                                placeholder="Description" ng-model="description"></textarea>

                                <div class="text-danger mt-1 text-xs" ng-show="addRoleForm.description.$touched && addRoleForm.description.$invalid">
                                    <span ng-show="addRoleForm.description.$error.required">* The description field is required.</span>
                                </div>
                            </div> 
                            
                            <div class="col-lg-6 mb-3">
                                <label for="status"> Status <span class="text-warning">*</span> </label>
                                <select id="status" name="status" class="form-control" ng-model="status" required>
                                    <option value=""> -- Status -- </option>
                                    <option value="ACTIVE"> Active </option>
                                    <option value="INACTIVE"> Inactive </option>
                                </select>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="addRoleForm.status.$touched && addRoleForm.status.$invalid">
                                    <span ng-show="addRoleForm.status.$error.required">* The status field is required.</span>
                                </div>
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

    <!-- Update Modal -->
    <div class="modal fade" id="updateModal" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white" id="updateModalLabel">Update Role</h5>
                    <button type="button" class="btn-close"data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    
                    <form name="updateForm" method="post" ng-submit="updateRole(roleId)">
                        @csrf
                        <div class="text-danger mb-2" id="updateRole-error-res"></div>
                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <label for="updateRoleName"> Role Name <span class="text-warning">*</span> </label>
                                <input id="updateRoleName" type="text" name="updateRoleName" class="form-control"
                                    placeholder="Role Name" ng-model="updateRoleName" required>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="updateForm.updateRoleName.$touched && updateForm.updateRoleName.$invalid">
                                    <span ng-show="updateForm.updateRoleName.$error.required">* The role name field is required.</span>
                                </div>
                            </div> 

                            <div class="col-lg-6 mb-3">
                                <label for="updateRoleCode"> Role Code <span class="text-warning">*</span> </label>
                                <input id="updateRoleCode" type="text" name="updateRoleCode" class="form-control"
                                    placeholder="Role Code" ng-model="updateRoleCode" required readonly>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="updateForm.updateRoleCode.$touched && updateForm.updateRoleCode.$invalid">
                                    <span ng-show="updateForm.updateRoleCode.$error.required">* The role code field is required.</span>
                                </div>
                            </div> 

                            <div class="col-lg-6 mb-3">
                                <label for="updateDescription"> Description </label>   
                                <textarea id="updateDescription" name="updateDescription" class="form-control"
                                placeholder="Description" ng-model="updateDescription"></textarea>

                                <div class="text-danger mt-1 text-xs" ng-show="updateForm.updateDescription.$touched && updateForm.updateDescription.$invalid">
                                    <span ng-show="updateForm.updateDescription.$error.required">* The description field is required.</span>
                                </div>
                            </div> 

                            <div class="col-lg-6 mb-3">
                                <label for="updateStatus"> Status <span class="text-warning">*</span> </label>
                                <select id="updateStatus" name="updateStatus" class="form-control" ng-model="updateStatus" required>
                                    <option value="ACTIVE"> Active </option>
                                    <option value="INACTIVE"> Inactive </option>
                                </select>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="updateForm.updateStatus.$touched && updateForm.updateStatus.$invalid">
                                    <span ng-show="updateForm.updateStatus.$error.required">The status field is required.</span>
                                </div>
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

</div>
@endsection
