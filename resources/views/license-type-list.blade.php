@extends('layouts.main')
@section('content')
<div class="container-fluid py-4" ng-controller="licenseTypeController">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">All License Types</h5>
                        </div>
                        <div class="d-flex flex-row">
                            <a href="javascript:void(0)" class="btn bg-gradient-primary mb-0 mx-2" type="button" data-bs-toggle="modal" data-bs-target="#addLicenseTypeModal" ng-click="clearFormData('addLicenseTypeForm'); getTodayDate()"><i class="fa fa-plus-circle"></i> Add License Type</a>
                            <!-- </div>
                            <div class="px-0 pt-5"> //Commented and moved in above - AR[06-Dec-2025]-->
                        
                            <a href="javascript:void(0)" class="btn bg-gradient-info mb-0 hide-on-load" ng-class="{show: totalData}" ng-click="exportLicenseType()"><i class="fas fa-file-export me-1"></i> Export Excel</a>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pt-3 pb-2">
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
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByField('name')">
                                        License Type Name <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByField('code')">
                                        License Type Code <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByField('expiry_duration')">
                                        Duration <i class="fa fa-sort" aria-hidden="true"></i>
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
                                        <input type="text" class="form-control" ng-model="durationFilter" ng-change="callPaginateData()" placeholder="Search Here...">
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
                                </tr>

                                <tr class="data-row" ng-repeat="licenseType in licenseTypes | orderBy: sortBy : reverse" ng-class="{show: licenseTypes}">
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="($index + dataFrom)"></p>
                                    </td>
                                    
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="licenseType.name"></p>
                                    </td>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="licenseType.code"></p>
                                    </td>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="licenseType.expiry_duration | date : 'dd-MM-y'"></p>
                                    </td>
                                    <td class="ps-4">
                                        <p class="text-sm font-weight-bold text-capitalize mb-0">
                                            <span class="badge bg-gradient-success status-badge-two" ng-if="licenseType.status == 'AVAILABLE'" ng-bind="licenseType.status | textCapitalize"></span>
                                            <span class="badge bg-gradient-danger status-badge-two" ng-if="licenseType.status == 'NOT_AVAILABLE'" ng-bind="licenseType.status | textCapitalize"></span>
                                        </p>
                                    </td>
                                    <td class="ps-4">
                                        <span class="text-secondary text-xs font-weight-bold" ng-bind="licenseType.created_at | strToDate | date : 'dd-MM-y hh:mm:ss a'"></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="javascript:void(0);" class="mx-1" data-bs-toggle="modal" data-bs-target="#updateModal" ng-click="getLicenseType(licenseType); getTodayDate();">
                                            <i class="fas fa-edit text-info"></i>
                                        </a>
                                        <a href="javascript:void(0);" class="mx-1" ng-show="(licenseType.status == 'NOT_AVAILABLE') ? true : false" data-bs-toggle="modal" data-bs-target="#alertModal" ng-click="getLicenseType(licenseType);">
                                            <i class="cursor-pointer fas fa-trash text-danger"></i>
                                        </a>
                                    </td>
                                </tr>

                                <tr class="no-data-found-row" ng-class="{show: licenseTypes.length == 0}">
                                    <td colspan="7" class="text-center text-secondary">No Data Found.</td>
                                </tr>

                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div  class="mt-5 pagination-container" ng-class="{show: licenseTypes.length > 0}">
                            <pagination></pagination>
                        </div>  
                        
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Add License Type Modal -->
    <div class="modal fade" id="addLicenseTypeModal" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="spinner-overlay" ng-show="spinnerLoading">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Saving...</span>
                    </div>
                </div>
                
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white" id="addLicenseTypeModalLabel">Add License Type</h5>
                    <button type="button" class="btn-close"data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <form name="addLicenseTypeForm" method="post" ng-submit="addLicenseType()">
                        @csrf
                        <div class="text-danger mb-2" id="addLicenseType-error-res"></div>
                        <div class="row">
                  
                            <div class="col-lg-6 mb-3">
                                <label for="licenseTypename"> License Type Name <span class="text-warning">*</span> </label>
                                <input id="licenseTypename" type="text" name="licenseTypename" class="form-control"
                                    placeholder="License Type Name" ng-model="licenseTypename" ng-blur="generateLicenseCode(licenseTypename)" required>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="addLicenseTypeForm.licenseTypename.$touched && addLicenseTypeForm.licenseTypename.$invalid">
                                    <span ng-show="addLicenseTypeForm.licenseTypename.$error.required">* The license type name field is required.</span>
                                </div>
                            </div> 

                            <div class="col-lg-6 mb-3">
                                <label for="licenseCode"> License Code <span class="text-warning">*</span> </label>
                                <input id="licenseCode" type="text" name="licenseCode" class="form-control"
                                    placeholder="License Code" ng-model="licenseCode" required>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="addLicenseTypeForm.licenseCode.$touched && addLicenseTypeForm.licenseCode.$invalid">
                                    <span ng-show="addLicenseTypeForm.licenseCode.$error.required">* The license code field is required.</span>
                                </div>
                            </div> 

                            <div class="col-lg-6 mb-3">
                                <label for="expiryPeriod"> Expiry Period <span class="text-warning">*</span> </label>
                                <select name="expiryPeriod" id="expiryPeriod" ng-model="expiryPeriod" class="form-control" ng-change="expiryDurationFormat(expiryPeriod)" required>
                                    <option value=""> -- Select Period -- </option>
                                    <option value="Day(s)">Day(s)</option>
                                    <option value="Month(s)">Month(s)</option>
                                    <option value="Year(s)">Year(s)</option>
                                    <option value="Date">Custom Date</option>
                                </select>

                                <div class="text-danger mt-1 text-xs" ng-show="addLicenseTypeForm.expiryPeriod.$touched && addLicenseTypeForm.expiryPeriod.$invalid">
                                    <span ng-show="addLicenseTypeForm.expiryPeriod.$error.required">* Please select the expiry period.</span>
                                </div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="expiryDuration"> Expiry Duration <span class="text-warning">*</span> </label>
                                <input id="expiryDuration" name="expiryDuration" class="form-control"
                                placeholder="Expiry Duration" ng-model="expiryDuration" ng-show="!expiryDurationDateFlag" ng-required="!expiryDurationDateFlag">

                                <div class="text-danger mt-1 text-xs" ng-show="addLicenseTypeForm.expiryDuration.$touched && addLicenseTypeForm.expiryDuration.$invalid">
                                    <span ng-show="addLicenseTypeForm.expiryDuration.$error.required">* The expiry duration field is required.</span>
                                </div>

                                <datepicker date-format="dd-MM-yyyy" ng-show="expiryDurationDateFlag" date-min-limit="[[ todayDate ]]">
                                    <div class="input-group">
                                        <input type="text" class="form-control" ng-model="expiryDurationDate" placeholder="Expiry Duration" ng-required="expiryDurationDateFlag">
                                        <span class="input-group-text" style="cursor: pointer">
                                            <i class="fa fa-lg fa-calendar"></i>
                                        </span>
                                    </div>

                                    <div class="text-danger mt-1 text-xs" ng-show="addLicenseTypeForm.expiryDurationDate.$touched && addLicenseTypeForm.expiryDurationDate.$invalid">
                                        <span ng-show="addLicenseTypeForm.expiryDurationDate.$error.required">* The expiry duration field is required.</span>
                                    </div>
                                </datepicker>                 
                                
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="description"> Description </label>
                                <textarea id="description" type="text" name="description" class="form-control"
                                    placeholder="Description" ng-model="description"></textarea>
                            </div>
                            
                            <div class="col-lg-6 mb-3">
                                <label for="status"> Status <span class="text-warning">*</span> </label>
                                <select id="status" name="status" class="form-control" ng-model="status" required>
                                    <option value=""> -- Status -- </option>
                                    <option value="AVAILABLE"> Available </option>
                                    <option value="NOT_AVAILABLE"> Not Available </option>
                                </select>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="addLicenseTypeForm.status.$touched && addLicenseTypeForm.status.$invalid">
                                    <span ng-show="addLicenseTypeForm.status.$error.required">* The status field is required.</span>
                                </div>
                            </div> 

                            <div class="text-sm mt-2">
                                <span class="text-dark font-weight-bolder"> Note: </span> <span class="text-warning"> * License code can't be changed once created. </span>
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
                <div class="spinner-overlay" ng-show="spinnerLoading">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Saving...</span>
                    </div>
                </div>
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white" id="updateModalLabel">Update License Type</h5>
                    <button type="button" class="btn-close"data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <form name="updateForm" method="post" ng-submit="updateLicenseType(licenseTypeId)">
                        @csrf
                        <div class="text-danger mb-2" id="updateLicenseType-error-res"></div>
                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <label for="updateLicenseTypename"> License Type Name <span class="text-warning">*</span> </label>
                                <input id="updateLicenseTypename" type="text" name="updateLicenseTypename" class="form-control"
                                    placeholder="License Type Name" ng-model="updateLicenseTypename" required>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="updateForm.updateLicenseTypename.$touched && updateForm.updateLicenseTypename.$invalid">
                                    <span ng-show="updateForm.updateLicenseTypename.$error.required">* The license type name field is required.</span>
                                </div>
                            </div> 

                            <div class="col-lg-6 mb-3">
                                <label for="updateLicenseCode"> License Code <span class="text-warning">*</span> </label>
                                <input id="updateLicenseCode" type="text" name="updateLicenseCode" class="form-control"
                                    placeholder="License Code" ng-model="updateLicenseCode" required readonly>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="updateForm.updateLicenseCode.$touched && updateForm.updateLicenseCode.$invalid">
                                    <span ng-show="updateForm.updateLicenseCode.$error.required">* The license type code field is required.</span>
                                </div>
                            </div> 

                            <div class="col-lg-6 mb-3">
                                <label for="updateExpiryDuration"> Expiry Period <span class="text-warning">*</span> </label>
                                <select name="updateExpiryPeriod" ng-model="updateExpiryPeriod" class="form-control" ng-change="expiryDurationFormat(updateExpiryPeriod)" required>
                                    <option value=""> -- Select Period -- </option>
                                    <option value="Day(s)">Day(s)</option>
                                    <option value="Month(s)">Month(s)</option>
                                    <option value="Year(s)">Year(s)</option>
                                    <option value="Date">Custom Date</option>
                                </select>

                                <div class="text-danger mt-1 text-xs" ng-show="updateForm.updateExpiryPeriod.$touched && updateForm.updateExpiryPeriod.$invalid">
                                    <span ng-show="updateForm.updateExpiryPeriod.$error.required">* Please select the expiry period.</span>
                                </div>                              
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="updateExpiryDuration"> Expiry Duration <span class="text-warning">*</span> </label>
                                <input id="updateExpiryDuration" name="updateExpiryDuration" class="form-control"
                                    placeholder="Expiry Duration" ng-model="updateExpiryDuration" ng-show="!expiryDurationDateFlag" ng-required="!expiryDurationDateFlag">
                                
                                    <div class="text-danger mt-1 text-xs" ng-show="updateForm.updateExpiryDuration.$touched && updateForm.updateExpiryDuration.$invalid">
                                    <span ng-show="updateForm.updateExpiryDuration.$error.required">* The expiry duration field is required.</span>
                                </div>
                                
                                <datepicker date-format="dd-MM-yyyy" ng-show="expiryDurationDateFlag" date-min-limit="[[ todayDate ]]">                                
                                    <div class="input-group">
                                        <input  type="text" class="form-control" ng-model="updateExpiryDurationDate" placeholder="Expiry Duration" ng-required="expiryDurationDateFlag">
                                        <span class="input-group-text" style="cursor: pointer">
                                            <i class="fa fa-lg fa-calendar"></i>
                                        </span>
                                    </div>
                                </datepicker>

                                <div class="text-danger mt-1 text-xs" ng-show="updateForm.updateExpiryDurationDate.$touched && updateForm.updateExpiryDurationDate.$invalid">
                                    <span ng-show="updateForm.updateExpiryDurationDate.$error.required">* The expiry duration field is required.</span>
                                </div>
                            </div>                             

                            <div class="col-lg-6 mb-3">
                                <label for="updateDescription"> Description </label>
                                <textarea id="updateDescription" type="text" name="updateDescription" class="form-control"
                                    placeholder="Description" ng-model="updateDescription"></textarea>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="updateStatus"> Status <span class="text-warning">*</span> </label>
                                <select id="updateStatus" name="updateStatus" class="form-control" ng-model="updateStatus" required>
                                    <option value="AVAILABLE"> Available </option>
                                    <option value="NOT_AVAILABLE"> Not Available </option>
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

    <!-- Alert Model -->
    <div class="modal fade" id="alertModal" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="spinner-overlay" ng-show="spinnerLoading">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Deleting...</span>
                    </div>
                </div>
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white" id="alertLabel">Caution</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-dark mb-2">
                        You’re about to delete the license type. This action will reflect on the Woo-Commerce website. Are you sure?
                    </div>
                    
                    <div class="col-md-12 mt-4 pt-4 border-top text-right">
                        <button type="button" class="btn bg-gradient-success" ng-click="deleteLicenseType(licenseTypeId)"> Yes </button>
                        <button type="button" class="btn bg-gradient-warning" data-bs-dismiss="modal"> No </button>
                    </div>
                </div>
             </div>
        </div>
    </div>

    <!-- Import License Type Modal -->
    <div class="modal fade" id="importModal" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white" id="importModalLabel">Import License Type</h5>
                    <button type="button" class="btn-close"data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    
                    <form name="importLicenseTypeForm" method="post" ng-submit="importLicenseType()">
                        @csrf
                        <div class="row">
                            <div class="col-sm-12 mb-4 mt-2 text-center">
                                <a href="files/templates/license_type_import_template.xlsx" class="btn bg-gradient-warning mb-0 w-60" download><i class="fa fa-download me-1"></i> Download Template</a>
                            </div>
                            <div class="col-sm-12 mb-2">
                                <label for="importFile"> Import File (.xlsx) <span class="text-warning">*</span> </label>
                                <input type="file" id="importFile" ng-model="importFile" name="importFile" class="form-control"
                                    required>
                            </div>
                            <div class="text-danger mb-2 text-sm" id="importProduct-error-res"></div>
                            <div class="text-sm mt-2">
                                <span class="text-dark font-weight-bolder"> Note: </span> 
                                <span class="text-warning ms-2"> * Product code can't be changed once created. </span>
                                <div class="text-warning ms-5"> * Please don't remove or edit the header row. </div>
                                <div class="text-warning ms-5"> * Product Prefix can't be changed once the license key is generated against the created Product. </div>
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
