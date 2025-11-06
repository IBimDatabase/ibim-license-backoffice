@extends('layouts.main')
@section('content')
<div class="container-fluid py-4" ng-controller="expiredLicenseController">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">All Expired License Keys</h5>
                        </div>
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
                        <div>
                            <a href="javascript:void(0)" class="btn bg-info mb-0 filter-toggle-btn text-white" type="button" ng-click="toggleExcludeTrial()" ng-class="{show: excludeTrial}" ng-bind="'Show Trial Licenses'"></a>
                            <a href="javascript:void(0)" class="btn bg-warning mb-0 filter-toggle-btn text-white" type="button" ng-click="toggleExcludeTrial()" ng-class="{show: !excludeTrial}" ng-bind="'Hide Trial Licenses'"></a>
                        
                            <a href="javascript:void(0)" class="btn bg-info mb-0 filter-toggle-btn text-white" type="button" ng-click="togglefilters()" ng-class="{show: !filtersToggle}" ng-bind="'Show Filters'"></a>
                            <a href="javascript:void(0)" class="btn bg-warning mb-0 filter-toggle-btn text-white" type="button" ng-click="togglefilters()" ng-class="{show: filtersToggle}" ng-bind="'Hide Filters'"></a>
                        </div>
                    </div>

                    <div class="table-responsive p-0 border-top">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-info text-xs font-weight-bolder opacity-7">
                                        Sno
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByField('product.product_name')">
                                        Product/Package Name <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByField('license_type')">
                                        License Type <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByField('license_key')">
                                        License Key <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <!-- <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByField('mac_address')">
                                        Mac Address <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th> -->
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByField('customer.email')">
                                        Customer Email <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByField('status')">
                                        Status <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <!-- <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByField('purchased_date')">
                                        Activation On <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByField('expiry_date')">
                                        Expired On <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th> -->
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody>

                                <tr class="filter-row" ng-class="{show: filtersToggle}">
                                    <td></td>
                                    <td>
                                        <input type="text" class="form-control" ng-model="productNameFilter" ng-change="callPaginateData()" placeholder="Search Here...">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" ng-model="licenseTypeFilter" ng-change="callPaginateData()" placeholder="Search Here...">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" ng-model="licenseFilter" ng-change="callPaginateData()" placeholder="Search Here...">
                                    </td>
                                    <!-- <td>
                                        <input type="text" class="form-control" ng-model="macAddressFilter" ng-change="callPaginateData()" placeholder="Search Here...">
                                    </td> -->
                                    <td>
                                        <input type="text" class="form-control" ng-model="emailFilter" ng-change="callPaginateData()" placeholder="Search Here...">
                                    </td>
                                    <td>
                                        <select class="form-control" ng-model="statusFilter" ng-change="getPaginateData()">
                                            <option value="EXPIRED"> Expired </option>
                                        </select>
                                    </td>  
                                    <!-- 
                                    <td>
                                        <datepicker date-format="dd-MM-yyyy">
                                            <input  type="text" class="form-control" ng-model="purchaseFromDateFilter" ng-change="getPaginateData()" placeholder="From...">
                                        </datepicker>
                                        <datepicker date-format="dd-MM-yyyy">
                                            <input  type="text" class="form-control mt-1" ng-model="purchaseToDateFilter" ng-change="getPaginateData()" placeholder="To...">
                                        </datepicker>
                                    </td>
                                    <td>
                                        <datepicker date-format="dd-MM-yyyy">
                                            <input  type="text" class="form-control" ng-model="expiryFromDateFilter" ng-change="getPaginateData()" placeholder="From...">
                                        </datepicker>
                                        <datepicker date-format="dd-MM-yyyy">
                                            <input  type="text" class="form-control mt-1" ng-model="expiryToDateFilter" ng-change="getPaginateData()" placeholder="To...">
                                        </datepicker>
                                    </td>
                                    -->
                                    <td></td>
                                </tr>

                                <tr class="data-row" ng-repeat="license in licenselist | orderBy: sortBy : reverse" ng-class="{show: licenselist}">
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="($index + dataFrom)"></p>
                                    </td>
                                    
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-show="!license.product.product_id" compile="(license.package.package_name) ? '<b class=\'text-sm\'>' + license.package.package_name + '</b><br>' + license.product.product_name : license.product.product_name"></p>
                                        <p class="text-xs font-weight-bold mb-0" ng-show="license.product.product_id" compile="(license.package.package_name) ? '<b class=\'text-sm\'>' + license.package.package_name + '</b><br>' + license.product.product_name + ' ('+ license.product.product_id + ')' : license.product.product_name + ' ('+ license.product.product_id + ')'"></p>
                                    </td>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="license.license_type"></p>
                                    </td>
                                    <td class="text-nowrap">
                                        <span class="text-xs font-weight-bold mb-0 me-1" ng-bind="license.hashed_license_key" id="LicenseKey-field-[[ ($index + 1) ]]"></span>
                                        <i class="fa fa-eye text-info cursor-pointer" data-bs-toggle="modal" data-bs-target="#showLicenseKeyModal" title="Reveal License Key" ng-click="getLicenseId(license, ($index + 1)); clearFormData('showLicenseForm')"></i>
                                        <i class="fas fa-copy text-primary copy-text cursor-pointer d-none" ng-click="copyToClipboard($event.target, 'LicenseKey-field-' + ($index + 1))" title="Copy"></i>
                                    </td>
                                    <!-- <td class="ps-4 text-nowrap">
                                        <span class="text-xs font-weight-bold me-1" ng-bind="license.mac_address"></span>
                                        <i class="fas fa-eraser text-danger cursor-pointer" data-bs-toggle="modal" data-bs-target="#alertModal" ng-click="getLicenseId(license, ($index + 1), 'EmptyMACAlert')" ng-show="license.mac_address" title="Empty MAC Address"></i>
                                    </td> -->
                                    <td class="ps-4">
                                        <span class="text-xs font-weight-bold" ng-bind="license.customer.email"></span>
                                    </td>
                                    <td class="ps-4">
                                        <p class="text-sm font-weight-bold text-capitalize mb-0">
                                            <span class="badge bg-gradient-danger" ng-bind="'EXPIRED'"></span>
                                        </p>
                                    </td>
                                    <!-- 
                                    <td class="ps-4 text-nowrap">
                                        <span class="text-secondary text-xs font-weight-bold" ng-bind="license.purchased_date | strToDate | date : 'dd-MM-y'"></span>
                                    </td>
                                    <td class="ps-4 text-nowrap">
                                        <span class="text-secondary text-xs font-weight-bold" ng-bind="license.expiry_date | strToDate | date : 'dd-MM-y'"></span>
                                    </td>
                                    -->
                                    <td class="text-center">
                                        <div class="col">
                                            <a href="javascript:void(0);" class="me-1" data-bs-toggle="modal" data-bs-target="#licenseDetailModal" ng-click="getlicenseDetails(license.license_uuid)" title="License Details">
                                                <i class="fas fa-eye text-info"></i>
                                            </a>
                                            <a href="javascript:void(0);" class="me-1" data-bs-toggle="modal" data-bs-target="#LicenseHistoryModal" ng-click="getLicenseHistory(license.license_uuid)" title="License History">
                                                <i class="fas fa-history text-info"></i>
                                            </a>
                                        </div>
                                        <div class="col">
                                            <a href="javascript:void(0);" class="me-1" data-bs-toggle="modal" data-bs-target="#renewModal" ng-click="getRenewData(license, license.product); getLicenseCodes(); clearFormData('renewForm');" title="Renew License">
                                                <i class="fa fa-refresh text-primary"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="no-data-found-row" ng-class="{show: licenselist.length == 0}">
                                    <td colspan="8" class="text-center text-secondary">No Data Found.</td>
                                </tr>
                                
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div  class="mt-5 pagination-container" ng-class="{show: licenselist.length > 0}">
                            <pagination></pagination>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- License History -->
    <div class="modal fade" id="LicenseHistoryModal" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white" id="LicenseHistoryModalLabel">License History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-info text-xs font-weight-bolder opacity-7">
                                    Sno
                                </th>
                                <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByHistoryField('entry_type')">
                                    Action <i class="fa fa-sort" aria-hidden="true"></i>
                                </th>
                                <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByHistoryField('license_key')">
                                    License Key <i class="fa fa-sort" aria-hidden="true"></i>
                                </th>
                                <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByHistoryField('mac_address')">
                                    Mac Address <i class="fa fa-sort" aria-hidden="true"></i>
                                </th>
                                <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByHistoryField('user_id')">
                                    Done By <i class="fa fa-sort" aria-hidden="true"></i>
                                </th>
                                <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByHistoryField('created_at')">
                                    Done On <i class="fa fa-sort" aria-hidden="true"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr ng-repeat="licenseHistory in licenseHistoryData | orderBy: sortByHistory: reverseHistory">
                                <td class="ps-4" ng-bind="($index + 1)"></td>
                                <td class="ps-4" ng-bind="(licenseHistory.entry_type) ? licenseHistory.entry_type : 'MAC Address Update' | textCapitalize : (licenseHistory.entry_type == 'MAC_ADDRESS_UPDATE' || !licenseHistory.entry_type) ? 1 : ''"></td>
                                <td class="ps-4" ng-bind="licenseHistory.hashed_license_key"></td>
                                <td class="ps-4" ng-bind="licenseHistory.mac_address"></td>
                                <td class="ps-4" ng-bind="(licenseHistory.user.last_name) ? licenseHistory.user.first_name + ' ' + licenseHistory.user.last_name: licenseHistory.user.first_name"></td>
                                <td class="ps-4" ng-bind="licenseHistory.created_at | strToDate | date : 'dd-MM-y hh:mm:ss a'"></td>
                            </tr>

                            <tr ng-show="licenseHistoryData.length == 0">
                                <td colspan="7" class="text-center text-secondary">No Data Found.</td>
                            </tr>
                        </tbody>

                    </table>

                </div>
             </div>
        </div>
    </div>

    <!-- License Detail Modal -->
    <div class="modal fade" id="licenseDetailModal" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white" id="licenseDetailModalLabel">License Details</h5>
                    <button type="button" class="btn-close"data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body bg-grey pb-4">  
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-secondary mb-1"> License Key :</div>
                            <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info" ng-bind="licenseDetail.hashed_license_key"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-secondary mb-1"> License Type :</div>
                            <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info" ng-bind="licenseDetail.license_type | textCapitalize"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-secondary mb-1"> Package Name :</div>
                            <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info" ng-bind="licenseDetail.package.package_name"></div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <div class="text-secondary mb-1"> Product Name :</div>
                            <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info" ng-bind="licenseDetail.product.product_name"></div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <div class="text-secondary mb-1"> Product ID :</div>
                            <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info" ng-bind="licenseDetail.product.product_id"></div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <div class="text-secondary mb-1"> MAC Address :</div>
                            <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info">
                                [[ licenseDetail.mac_address ]]
                            </div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <div class="text-secondary mb-1"> Created On :</div>
                            <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info" ng-bind="licenseDetail.created_at | strToDate | date : 'dd-MM-y hh:mm:ss a'"></div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <div class="text-secondary mb-1"> Activation On :</div>
                            <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info" ng-bind="licenseDetail.purchased_date | strToDate | date : 'dd-MM-y'"></div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <div class="text-secondary mb-1"> Expiry On :</div>
                            <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info" ng-bind="licenseDetail.expiry_date | strToDate | date : 'dd-MM-y'"></div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <div class="text-secondary mb-1"> Status :</div>
                            <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info">Expired</div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <div class="text-secondary mb-1"> Order Source :</div>
                            <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info" ng-bind="licenseDetail.order.order_source"></div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <div class="text-secondary mb-1"> Order Reference No :</div>
                            <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info" ng-bind="licenseDetail.order.order_reference_no"></div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <div class="text-secondary mb-1"> Order Info :</div>
                            <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info" ng-bind="licenseDetail.order.order_info"></div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <div class="text-secondary mb-1"> Order Time :</div>
                            <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info" ng-bind="licenseDetail.order.order_placed_at | strToDate | date : 'dd-MM-y'"></div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <div class="text-secondary mb-1"> Customer Name :</div>
                            <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info" ng-bind="(licenseDetail.customer.last_name) ? licenseDetail.customer.first_name + ' ' + licenseDetail.customer.last_name : licenseDetail.customer.first_name"></div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <div class="text-secondary mb-1"> Customer Email :</div>
                            <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info" ng-bind="licenseDetail.customer.email"></div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <div class="text-secondary mb-1"> Customer Phone :</div>
                            <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info" ng-bind="licenseDetail.customer.phone"></div>
                        </div>    
                    </div> 
                </div>
             </div>
        </div>
    </div>

    <!-- Renew Modal -->
    <div class="modal fade" id="renewModal" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white" id="renewModalLabel">Renew License</h5>
                    <button type="button" class="btn-close"data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-danger mb-2" id="renewLicense-error-res"></div>
                    <form name="renewForm" method="post" ng-submit="renewLicenseDetails()">
                        @csrf
                        <input id="renewLicenseId" type="hidden" ng-model="renewLicenseId">
                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <label for="renewLicense"> License Key <span class="text-warning">*</span> </label>
                                <input id="renewLicense" type="text" name="renewLicense" class="form-control"
                                    placeholder="License Key" ng-model="renewLicense" readonly>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="renewProductCode"> Product Code <span class="text-warning">*</span> </label>
                                <input id="renewProductCode" type="text" name="renewProductCode" class="form-control"
                                    placeholder="Product Code" ng-model="renewProductCode" readonly>
                            </div>   

                            <div class="col-lg-6 mb-3">
                                <label for="renewLicenseCode"> License Code <span class="text-warning">*</span> </label>
                                <select id="renewLicenseCode" name="renewLicenseCode" class="form-control" ng-model="renewLicenseCode" required>
                                    <option value=""> -- Select License Code -- </option>
                                    <option ng-repeat="licenseCode in licenseCodes" value="[[ licenseCode.code ]]">[[ licenseCode.name ]]</option>
                                </select>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="renewForm.renewLicenseCode.$touched && renewForm.renewLicenseCode.$invalid">
                                    <span ng-show="renewForm.renewLicenseCode.$error.required">* The license code field is required.</span>
                                </div>
                            </div>   
                            
                            <div class="col-lg-6 mb-3" ng-show="renewPackageFlag">
                                <label for="licenseCode"> Renewal For <span class="text-warning">*</span> </label>
                                <div>
                                    <input id="productType" type="radio" name="renewalType" ng-model="renewalType" value="PRODUCT_CODE" ng-required="renewPackageFlag"> <label for="productType">Product Only</label> 
                                    <input id="packageType" type="radio" name="renewalType" class="ms-2" ng-model="renewalType" value="PACKAGE" ng-required="renewPackageFlag"> <label for="packageType">Package</label>
                                </div>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="updateForm.updatePhoneNo.$touched">
                                    <span ng-show="updateForm.updatePhoneNo.$error.required">* The phone no. field is required.</span>
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


    <!-- Show License Key Modal -->
    <div class="modal fade" id="showLicenseKeyModal" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white" id="showLicenseKeyModalLabel">Reveal License Key</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-danger mb-2" id="getActualLicenseKey-error-res"></div>
                    <form name="showLicenseForm" method="post" ng-submit="getActualLicenseKey()">
                        @csrf
                        <div class="row">
                            <input type="hidden" name="licenseId" ng-model="licenseId">
                            <div class="col-lg-12 mb-3">
                                <label for="password"> Password </label>
                                <div class="input-group">
                                    <input id="password" type="[[ (passwordToggle == true) ? 'password' : 'text' ]]" name="password" class="form-control"
                                    placeholder="Password" ng-model="password" aria-describedby="input-addon" required>
                                    <span class="input-group-text" id="input-addon"><i class="fa fa-eye cursor-pointer" ng-click="togglePassword()"></i></span>
                                </div>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="showLicenseForm.password.$touched && showLicenseForm.password.$invalid">
                                    <span ng-show="showLicenseForm.password.$error.required">The password field is required.</span>
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


    <!-- Empty The MAC Address Modal -->
    <div class="modal fade" id="alertModal" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white" id="alertLabel">Caution</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-dark mb-2" ng-if="alertType == 'EmptyMACAlert'">
                        You’re about to clear the MAC Address. This action leads to revoke the access to the user device. Are you sure?
                    </div>

                    <div class="text-dark mb-2" ng-if="alertType == 'DeactivateAlert'">
                        You’re about to deactivate the license key. Are you sure?
                    </div>

                    <div class="text-dark mb-2" ng-if="alertType == 'ActivateAlert'">
                        You’re about to activate the license key. Are you sure?
                    </div>
                    
                    <div class="text-dark mb-2" ng-if="alertType == 'DeleteAlert'">
                        You’re about to delete the license key. To delete, please enter your login password.
                        <div class="text-danger mt-3" id="deleteLicenseKey-error-res"></div>
                        <form name="deleteLicenseForm" method="post" ng-submit="deleteLicenseKey()"> 
                            @csrf
                            <div class="row">
                                <input type="hidden" name="licenseId" ng-model="licenseId">
                                <div class="col-lg-12 mb-3 mt-3">
                                    <div class="input-group">
                                        <input id="userPassword" type="[[ (userPasswordToggle == true) ? 'password' : 'text' ]]" name="userPassword" class="form-control"
                                        placeholder="Password" ng-model="formData.userPassword" ng-change="enterValue()" aria-describedby="input-addon" required>
                                        <span class="input-group-text" id="input-addon"><i class="fa fa-eye cursor-pointer" ng-click="toggleUserPassword()"></i></span>
                                    </div>
                                    
                                    <div class="text-danger mt-1 text-xs" ng-show="deleteLicenseForm.userPassword.$touched && deleteLicenseForm.userPassword.$invalid">
                                        <span ng-show="deleteLicenseForm.userPassword.$error.required">The password field is required.</span>
                                    </div>
                                </div> 
                                
                                <div class="col-md-12 mt-4 pt-4 border-top text-right">
                                    <button type="submit" class="btn bg-gradient-warning"> Submit </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="col-md-12 mt-4 pt-4 border-top text-right" ng-if="alertType != 'DeleteAlert'">
                        <button type="submit" class="btn bg-gradient-success" ng-click="(alertType == 'EmptyMACAlert') ? emptyTheMACAddress(licenseId) : (alertType == 'ActivateAlert') ? activateLicense(licenseId) : deactivateLicense(licenseId)"> Yes </button>
                        <button type="button" class="btn bg-gradient-warning" data-bs-dismiss="modal"> No </button>
                    </div>
                    
                </div>
             </div>
        </div>
    </div>


    <!-- MAC Address History -->
    <div class="modal fade" id="MAChistoryModal" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white" id="MAChistoryModalLabel">MAC Address History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-info text-xs font-weight-bolder opacity-7">
                                    Sno
                                </th>
                                <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByHistoryField('license_key')">
                                    License Key <i class="fa fa-sort" aria-hidden="true"></i>
                                </th>
                                <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByHistoryField('mac_address')">
                                    Mac Address <i class="fa fa-sort" aria-hidden="true"></i>
                                </th>
                                <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByHistoryField('user_id')">
                                    Updated By <i class="fa fa-sort" aria-hidden="true"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr ng-repeat="macHistory in macHistoryData | orderBy: sortByHistory: reverseHistory">
                                <td class="ps-4" ng-bind="($index + 1)"></td>
                                <td class="ps-4" ng-bind="macHistory.license_key"></td>
                                <td class="ps-4" ng-bind="macHistory.mac_address"></td>
                                <td class="ps-4" ng-bind="(macHistory.user.last_name) ? macHistory.user.first_name + ' ' + macHistory.user.last_name: macHistory.user.first_name"></td>
                            </tr>

                            <tr ng-show="macHistoryData.length == 0">
                                <td colspan="4" class="text-center text-secondary">No Data Found.</td>
                            </tr>
                        </tbody>

                    </table>

                </div>
             </div>
        </div>
    </div>

</div>
@endsection
