@extends('layouts.main')
@section('content')
<div class="container-fluid py-4" ng-controller="packageController">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">All Packages</h5>
                        </div>
                        <a href="javascript:void(0)" class="btn bg-gradient-primary mb-0" type="button" data-bs-toggle="modal" data-bs-target="#addModal" ng-click="clearFormData('addPackageForm'); getProducts()"><i class="fa fa-plus-circle me-1"></i> Add Package</a>
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
                                    <th class="text-uppercase text-info text-xs font-weight-bolder">
                                        Sno
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder" ng-click="sortByField('package_name')">
                                        Package Name <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder" ng-click="sortByField('package_code')">
                                        Package Code <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-uppercase text-info text-xs font-weight-bolder" ng-click="sortByField('package_code')">
                                        Products <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder" ng-click="sortByField('status')">
                                        Status <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder" ng-click="sortByField('created_at')">
                                        Creation On <i class="fa fa-sort" aria-hidden="true"></i>
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
                                        <input type="text" class="form-control" ng-model="codeFilter" ng-change="callPaginateData()" placeholder="Search Here...">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" ng-model="productCodesFilter" ng-change="callPaginateData()" placeholder="Search Here...">
                                    </td>
                                    <td>
                                        <select class="form-control" ng-model="statusFilter" ng-change="getPaginateData()">
                                            <option value=""> -- Status -- </option>
                                            <option value="AVAILABLE"> Available </option>
                                            <option value="NOT_AVAILABLE"> Not Available </option>
                                        </select>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>

                                <tr class="data-row" ng-repeat="package in packages | orderBy: sortBy: reverse" ng-class="{show: packages}">
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="($index + dataFrom)"></p>
                                    </td>
                                    
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="package.package_name"></p>
                                    </td>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="package.package_code"></p>
                                    </td>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" compile="displayShortProductNames(package.product_codes, ($index + 1))"></p>
                                    </td>
                                    <td class="text-center">
                                        <p class="text-sm font-weight-bold text-capitalize mb-0">
                                            <span class="badge bg-gradient-success status-badge-two" ng-if="package.status == 'AVAILABLE'" ng-bind="package.status | textCapitalize"></span>
                                            <span class="badge bg-gradient-danger status-badge-two" ng-if="package.status == 'NOT_AVAILABLE'" ng-bind="package.status | textCapitalize"></span>
                                        </p>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <span class="text-secondary text-xs font-weight-bold" ng-bind="package.created_at | strToDate | date : 'dd-MM-y hh:mm:ss a'"></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="javascript:void(0);" class="mx-1" data-bs-toggle="modal" data-bs-target="#updateModal" ng-click="getPackageData(package); getProducts()">
                                            <i class="fas fa-edit text-info"></i>
                                        </a> 
                                        <!--  <a href="javascript:void(0);" class="mx-1" data-bs-toggle="tooltip" ng-click="deletePackage(package.id)">
                                            <i class="cursor-pointer fas fa-trash text-danger"></i>
                                        </a> -->
                                    </td>
                                </tr>

                                <tr class="no-data-found-row" ng-class="{show: packages.length == 0}">
                                    <td colspan="7" class="text-center text-secondary">No Data Found.</td>
                                </tr>
                            
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="mt-5 pagination-container" ng-class="{show: packages.length > 0}">
                            <pagination></pagination>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Package Modal -->
    <div class="modal fade" id="addModal" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white" id="addModalLabel">Add Package</h5>
                    <button type="button" class="btn-close"data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    
                    <form name="addPackageForm" method="post" ng-submit="addPackage()">
                        @csrf
                        <div class="text-danger mb-2 text-sm" id="addPackage-error-res"></div>
                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <label for="packageName"> Package Name <span class="text-warning">*</span> </label>
                                <input id="packageName" type="text" name="packageName" class="form-control"
                                    placeholder="Package Name" ng-model="packageName" ng-blur="generatePackageCode(packageName)" required>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="addPackageForm.packageName.$touched && addPackageForm.packageName.$invalid">
                                    <span ng-show="addPackageForm.packageName.$error.required">* The package name field is required.</span>
                                </div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="packageCode"> Package Code <span class="text-warning">*</span> </label>
                                <input id="packageCode" type="text" name="packageCode" class="form-control"
                                    placeholder="Package Code" ng-model="packageCode" required>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="addPackageForm.packageCode.$touched && addPackageForm.packageCode.$invalid">
                                    <span ng-show="addPackageForm.packageCode.$error.required">* The package code field is required.</span>
                                </div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label> Products <span class="text-warning">*</span> </label>
                                <div ng-dropdown-multiselect="" options="productCodes[0]" selected-model="selectedProductCodes" extra-settings="productCodesDropdownSetting" translation-texts="productCodesDropdownText"></div>                           
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="status"> Status <span class="text-warning">*</span> </label>
                                <select id="status" name="status" class="form-control" ng-model="status" required>
                                    <option value=""> -Select- </option>
                                    <option value="AVAILABLE"> Available </option>
                                    <option value="NOT_AVAILABLE"> Not Available </option>
                                </select>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="addPackageForm.status.$touched && addPackageForm.status.$invalid">
                                    <span ng-show="addPackageForm.status.$error.required">* The status field is required.</span>
                                </div>
                            </div>   

                            <div class="col-lg-12 mb-3">
                                <input type="checkbox" id="exclusivePackage" name="exclusivePackage" class="form-check-input" ng-model="exclusivePackage" ng-true-value="'YES'" ng-false-value="'NO'"> 
                                <label class="form-check-label" for="exclusivePackage"> Exclusive Package </label>
                                (<small class="text-dark text-bolder text-xs">The newly added products will be available to exist users</small>).
                            </div>

                            <div class="text-sm mt-2">
                                <span class="text-dark font-weight-bolder"> Note: </span> <span class="text-warning"> * Package code can't be changed once created. </span>
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
                    <h5 class="modal-title text-white" id="updateModalLabel">Update Package</h5>
                    <button type="button" class="btn-close"data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    
                    <form name="updateForm" method="post" ng-submit="updatePackage(packageId)">
                        @csrf
                        <div class="text-danger mb-2" id="updatePackage-error-res"></div>
                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <label for="updatePackageName"> Package Name <span class="text-warning">*</span> </label>
                                <input id="updatePackageName" type="text" name="updatePackageName" class="form-control"
                                    placeholder="Package Name" ng-model="updatePackageName" required>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="updateForm.updatePackageName.$touched && updateForm.updatePackageName.$invalid">
                                    <span ng-show="updateForm.updatePackageName.$error.required">* The package name field is required.</span>
                                </div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="updatePackageCode"> Package Code <span class="text-warning">*</span> </label>
                                <input id="updatePackageCode" type="text" name="updatePackageCode" class="form-control"
                                ng-model="updatePackageCode" readonly>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label> Products <span class="text-warning">*</span> </label>
                                <div ng-dropdown-multiselect="" options="productCodes[0]" selected-model="updateSelectedProductCodes" extra-settings="productCodesDropdownSetting" translation-texts="productCodesDropdownText"></div>                       
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="updateStatus"> Status <span class="text-warning">*</span> </label>
                                <select id="updateStatus" name="updateStatus" class="form-control" ng-model="updateStatus" required>
                                    <option value="AVAILABLE"> Available </option>
                                    <option value="NOT_AVAILABLE"> Not Available </option>
                                </select>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="updateForm.updateStatus.$touched && updateForm.updateStatus.$invalid">
                                    <span ng-show="updateForm.updateStatus.$error.required">* The status field is required.</span>
                                </div>
                            </div>   

                            <div class="col-lg-12 mb-3">
                                <input type="checkbox" id="updateExclusivePackage" name="updateExclusivePackage" class="form-check-input" ng-model="updateExclusivePackage" ng-true-value="'YES'" ng-false-value="'NO'"> 
                                <label class="form-check-label" for="updateExclusivePackage"> Exclusive Package </label> 
                                (<small class="text-dark text-bolder text-xs">The newly added products will be available to exist users</small>).
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
