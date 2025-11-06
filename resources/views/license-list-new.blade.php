@extends('layouts.main')
@section('content')
<style>
    .nav-link {
        color: #495057;
    }

    .nav-link:hover,
    .nav-link:focus {
      color: unset;
    }

    .nav-link.active {
      color: #f53939 !important;
    }
    .date-filter {
        display: flex;
        flex-wrap: nowrap;
        flex-direction: row;
        justify-content: flex-start;
        align-items: center;
        gap: 20px;
    }
    .date-filter>datepicker{
        max-width: 250px;
        margin: 0;
    }
    </style>
    <div class="container-fluid py-4" ng-controller="licenseControllerV1">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4 mx-4">
                    <div class="card-header pb-0">
                        <div class="d-flex flex-row justify-content-between">
                            <div>
                                <h5 class="mb-0">All License Keys</h5>
                            </div>
                            <a href="#" class="btn bg-gradient-primary mb-0" type="button" data-bs-toggle="modal"
                                data-bs-target="#generateLicenseModal"
                                ng-click="getCodes(); clearFormData('generateLicenseForm'); getYesterdayDate();"><i
                                    class="fa fa-plus-circle me-1"></i> Generate License Key</a>
                        </div>
                    </div>
                    <div class="card-header py-1 px-0">
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                            <button class="nav-link" ng-class="{ 'active': activeTab === 'PRODUCT' }" id="nav-product-tab"
                                data-bs-toggle="tab" data-bs-target="#nav-product" type="button" role="tab"
                                aria-controls="nav-product" aria-selected="{activeTab === 'PRODUCT'}"
                                ng-click="switchTab('PRODUCT')">Product Licenses</button>
                            <button class="nav-link" ng-class="{ 'active': activeTab === 'PACKAGE' }" id="nav-package-tab"
                                data-bs-toggle="tab" data-bs-target="#nav-package" type="button" role="tab"
                                aria-controls="nav-package" aria-selected="{activeTab === 'PACKAGE'}"
                                ng-click="switchTab('PACKAGE')">Package Licenses</button>
                        </div>
                    </div>
                    {{-- <div class="m-2 row date-filter">
                        <datepicker date-format="dd-MM-yyyy">
                            <input  type="text" class="form-control" ng-model="expiryFromDateFilter" ng-change="applyDateFilter()" placeholder="From...">
                        </datepicker>
                        <datepicker date-format="dd-MM-yyyy">
                            <input  type="text" class="form-control mt-1 py-2" ng-model="expiryToDateFilter" ng-change="applyDateFilter()" placeholder="To...">
                        </datepicker>
                        <button ng-class="{show: expiryFromDateFilter && expiryToDateFilter}" type="button" class="hide-on-load btn bg-warning mb-0 mt-1 rounded-5 w-10 text-white" ng-click="clearDateFilter()">
                            Clear
                        </button>
                        <button ng-class="{show: expiryFromDateFilter && expiryToDateFilter && showDownload && licenselist.length > 0 && !loading}" type="button" class="hide-on-load btn bg-info mb-0 mt-1 rounded-5 w-20 text-white" ng-click="downloadExcel()">
                            Download Excel
                        </button>
                    </div> --}}
                    <div class="tab-content" id="nav-tabContent">
                        <div class="tab-pane fade" ng-class="{ 'show active': activeTab === 'PRODUCT'}" id="nav-product"
                            role="tabpanel" aria-labelledby="nav-product-tab">
                            <div class="card-body px-0 pt-1 pb-2">
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
                                    <div class="pt-2 font-weight-bolder text-dark hide-on-load"
                                        ng-class="{show: !totalData}" ng-bind="'No Results'"></div>
                                    <div>
                                        <a href="javascript:void(0)"
                                            class="btn bg-dark mb-0 px-3 global-filter-toggle-btn mt-1 rounded-0 text-white"
                                            type="button" ng-click="globaltogglefilters()" ng-class="{show: 1}">
                                            <i ng-show="!globalFiltersToggle" class="fa fa-search fa-2x"></i>
                                            <i ng-show="globalFiltersToggle" class="fa fa-times fa-2x"></i>
                                        </a>
                                        <a href="javascript:void(0)"
                                            class="btn bg-info mb-0 mt-1 rounded-0 filter-toggle-btn text-white"
                                            type="button" ng-click="togglefilters()" ng-class="{show: !filtersToggle}"
                                            ng-bind="'Show Filters'"></a>
                                        <a href="javascript:void(0)"
                                            class="btn bg-warning mb-0 mt-1 rounded-0 filter-toggle-btn text-white"
                                            type="button" ng-click="togglefilters()" ng-class="{show: filtersToggle}"
                                            ng-bind="'Hide Filters'"></a>
                                    </div>
                                </div>

                                <div class="table-responsive p-0 border-top">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-info text-xs font-weight-bolder opacity-7">
                                                    Sno
                                                </th>
                                                <th class="text-left text-uppercase text-info text-xs font-weight-bolder opacity-7"
                                                    ng-click="sortByField('product.product_name')">
                                                    Product Name <i class="fa fa-sort" aria-hidden="true"></i>
                                                </th>
                                                <th class="text-left text-uppercase text-info text-xs font-weight-bolder opacity-7"
                                                    ng-click="sortByField('license_type')">
                                                    License Type <i class="fa fa-sort" aria-hidden="true"></i>
                                                </th>
                                                <th class="text-left text-uppercase text-info text-xs font-weight-bolder opacity-7"
                                                    ng-click="sortByField('license_key')">
                                                    License Key <i class="fa fa-sort" aria-hidden="true"></i>
                                                </th>
                                                <th class="text-left text-uppercase text-info text-xs font-weight-bolder opacity-7"
                                                    ng-click="sortByField('customer.email')">
                                                    Customer Email <i class="fa fa-sort" aria-hidden="true"></i>
                                                </th>
                                                <th class="text-left text-uppercase text-info text-xs font-weight-bolder opacity-7"
                                                    ng-click="sortByField('status')">
                                                    Status <i class="fa fa-sort" aria-hidden="true"></i>
                                                </th>
                                                <th
                                                    class="text-left text-uppercase text-info text-xs font-weight-bolder opacity-7">
                                                    Action
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            <tr class="global-search-row" ng-class="{show: globalFiltersToggle}">
                                                <td colspan="4"></td>
                                                <td colspan="3">
                                                    <input type="text" class="form-control"
                                                        ng-model="licenseGlobalSearch" ng-change="callPaginateData()"
                                                        placeholder="Search Here...">
                                                </td>
                                            </tr>

                                            <tr class="filter-row" ng-class="{show: filtersToggle}">
                                                <td></td>
                                                <td>
                                                    <input type="text" class="form-control"
                                                        ng-model="productNameFilter" ng-change="callPaginateData()"
                                                        placeholder="Search Here...">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control"
                                                        ng-model="licenseTypeFilter" ng-change="callPaginateData()"
                                                        placeholder="Search Here...">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" ng-model="licenseFilter"
                                                        ng-change="callPaginateData()" placeholder="Search Here...">
                                                </td>
                                                <!--<td>
                                                                <input type="text" class="form-control" ng-model="macAddressFilter" ng-change="callPaginateData()" placeholder="Search Here...">
                                                            </td>
                                                            <td>
                                                                <input type="text" class="form-control" ng-model="orderReferenceNoFilter" ng-change="callPaginateData()" placeholder="Search Here...">
                                                            </td>
                                                            <td>
                                                                <input type="text" class="form-control" ng-model="orderSourceFilter" ng-change="callPaginateData()" placeholder="Search Here...">
                                                            </td>
                                                            -->
                                                <td>
                                                    <input type="text" class="form-control" ng-model="emailFilter"
                                                        ng-change="callPaginateData()" placeholder="Search Here...">
                                                </td>
                                                <td>
                                                    <select class="form-control" ng-model="statusFilter"
                                                        ng-change="getPaginateData()">
                                                        <option value=""> -- Status -- </option>
                                                        <option value="AVAILABLE"> Available </option>
                                                        <option value="PURCHASED"> Activated </option>
                                                        <option value="DEACTIVATED"> Deactivated </option>
                                                    </select>
                                                </td>
                                                <!-- <td>
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
                                                            <td></td> -->
                                                <td></td>
                                            </tr>

                                            <tr class="data-row"
                                                ng-repeat="license in licenselist | orderBy: sortBy : reverse"
                                                ng-class="{show: licenselist}">
                                                <td class="ps-4">
                                                    <p class="text-xs font-weight-bold mb-0"
                                                        ng-bind="($index + dataFrom)"></p>
                                                </td>
                                                <td class="ps-4">
                                                    <p class="text-xs font-weight-bold mb-0"
                                                        ng-show="!license.product.product_id"
                                                        compile="(license.package.package_name) ? '<b class=\'text-sm\'>' + license.package.package_name + '</b><br>' + license.product.product_name : license.product.product_name">
                                                    </p>
                                                    <p class="text-xs font-weight-bold mb-0"
                                                        ng-show="license.product.product_id"
                                                        compile="(license.package.package_name) ? '<b class=\'text-sm\'>' + license.package.package_name + '</b><br>' + license.product.product_name + ' ('+ license.product.product_id + ')' : license.product.product_name + ' ('+ license.product.product_id + ')'">
                                                    </p>
                                                </td>
                                                <td class="ps-4">
                                                    <p class="text-xs font-weight-bold mb-0"
                                                        ng-bind="license.license_type | textCapitalize"></p>
                                                </td>
                                                <td class="text-nowrap">
                                                    <span class="text-xs font-weight-bold mb-0 me-1"
                                                        ng-bind="license.license_key || license.hashed_license_key"
                                                        id="LicenseKey-field-[[ ($index + 1) ]]"></span>
                                                    <i class="fa fa-eye text-info cursor-pointer" data-bs-toggle="modal"
                                                        data-bs-target="#showLicenseKeyModal" title="Reveal License Key"
                                                        ng-click="getLicenseId(license, ($index + 1)); clearFormData('showLicenseForm')"
                                                        ng-show="!['SUPER_ADMIN', 'ADMIN'].includes(authenticatedUser.user_type)"></i>
                                                    <i class="fas fa-copy text-primary copy-text cursor-pointer d-none"
                                                        ng-click="copyToClipboard($event.target, 'LicenseKey-field-' + ($index + 1))"
                                                        title="Copy"></i>
                                                </td>
                                                <!--
                                                            <td class="ps-4 text-nowrap">
                                                                <span class="text-xs font-weight-bold me-1" ng-bind="license.mac_address"></span>
                                                                <i class="fas fa-eraser text-danger cursor-pointer" data-bs-toggle="modal" data-bs-target="#alertModal" ng-click="getLicenseId(license, ($index + 1), 'EmptyMACAlert')" ng-show="license.mac_address" title="Reset MAC Address"></i>
                                                            </td>
                                                            <td class="ps-4">
                                                                <span class="text-xs font-weight-bold" ng-bind="license.order.order_reference_no"></span>
                                                            </td>
                                                            <td class="ps-4">
                                                                <span class="text-xs font-weight-bold" ng-bind="license.order.order_source"></span>
                                                            </td>
                                                            -->
                                                <td class="ps-4">
                                                    <span class="text-xs font-weight-bold"
                                                        ng-bind="license.customer.email"></span>
                                                </td>
                                                <td class="ps-4">
                                                    <p class="text-sm font-weight-bold text-capitalize mb-0">
                                                        <span class="badge bg-gradient-warning status-badge-two"
                                                            ng-if="license.status == 'AVAILABLE'"
                                                            ng-bind="license.status | textCapitalize"></span>
                                                        <span class="badge bg-gradient-success status-badge-two"
                                                            ng-if="license.status == 'PURCHASED'"
                                                            ng-bind="'Activated' | textCapitalize"></span>
                                                        <span class="badge bg-gradient-danger status-badge-two"
                                                            ng-if="license.status == 'EXPIRED'"
                                                            ng-bind="license.status | textCapitalize"></span>
                                                        <span class="badge bg-gradient-danger status-badge-two"
                                                            ng-if="license.status == 'DEACTIVATED'"
                                                            ng-bind="license.status | textCapitalize"></span>
                                                    </p>
                                                </td>

                                                <td class="text-center">
                                                    <!-- <a href="javascript:void(0);" class="me-1" data-bs-toggle="modal" data-bs-target="#updateModal" ng-if="license.status != 'EXPIRED' && license.status != 'DEACTIVATED'" ng-click="getCustomerDetails(license.id)" title="Update">
                                                                    <i class="fas fa-edit text-info"></i>
                                                                </a> -->
                                                    <div class="col">
                                                        <a href="javascript:void(0);" class="me-1"
                                                            data-bs-toggle="modal" data-bs-target="#licenseDetailModal"
                                                            ng-click="getlicenseDetails(license.license_uuid)"
                                                            title="License Details">
                                                            <i class="fas fa-eye text-info"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" class="me-1"
                                                            data-bs-toggle="modal" data-bs-target="#LicenseHistoryModal"
                                                            ng-click="getLicenseHistory(license.license_uuid)"
                                                            title="License History">
                                                            <i class="fas fa-history text-info"></i>
                                                        </a>
                                                    </div>

                                                    <div class="col">
                                                        <a href="javascript:void(0);" class="me-1"
                                                            ng-class="{ 'empty-div': license.status !== 'PURCHASED' }"
                                                            ng-if="license.status == 'PURCHASED' && license.status != 'DEACTIVATED'"
                                                            data-bs-toggle="modal" data-bs-target="#alertModal"
                                                            ng-click="getLicenseId(license, '', 'DeactivateAlert');"
                                                            title="Deactivate License">
                                                            <i class="fas fa-ban text-warning"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" class="me-1"
                                                            ng-if="license.status == 'DEACTIVATED'" data-bs-toggle="modal"
                                                            data-bs-target="#alertModal"
                                                            ng-click="getLicenseId(license, '', 'ActivateAlert');"
                                                            title="Activate License">
                                                            <i class="fa fa-hand-o-right text-success"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" class="me-1"
                                                            data-bs-toggle="modal" data-bs-target="#alertModal"
                                                            ng-click="getLicenseId(license, '', 'DeleteAlert');"
                                                            title="Delete License">
                                                            <i class="fas fa-trash text-danger"></i>
                                                        </a>
                                                    </div>

                                                    <div class="col">
                                                        <a href="javascript:void(0);" class="me-1"
                                                            data-bs-toggle="modal" data-bs-target="#renewModal"
                                                            ng-click="getRenewData(license, license.product); getLicenseCodes();"
                                                            ng-show="['PURCHASED', 'EXPIRED'].includes(license.status)"
                                                            title="Renew License">
                                                            <i class="fa fa-refresh text-primary"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" class="me-1"
                                                            data-bs-toggle="modal" data-bs-target="#alertModal"
                                                            ng-click="getLicenseId(license, '', 'ResetMACAlert');"
                                                            ng-show="license.status == 'PURCHASED'"
                                                            title="Reset Mac License">
                                                            <i class="fas fa-eraser text-danger"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr class="no-data-found-row" ng-class="{show: licenselist.length == 0}">
                                                <td colspan="7" class="text-center text-secondary">No Data Found.</td>
                                            </tr>

                                        </tbody>
                                    </table>

                                    <!-- Pagination -->
                                    <div class="mt-5 pagination-container" ng-class="{show: licenselist.length > 0}">
                                        <pagination></pagination>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" ng-class="{ 'show active': activeTab === 'PACKAGE'}" id="nav-package"
                            role="tabpanel" aria-labelledby="nav-package-tab">
                            <div class="card-body px-0 pt-1 pb-2">
                                <!-- loader -->
                                <div class="loader-overlay" ng-show="loading">
                                    <div class="loader-gif"></div>
                                </div>

                                <div class="px-4 py-2 bg-light-gray d-flex flex-row justify-content-between">
                                    <div class="pt-2 hide-on-load" ng-class="{show: totalData}">
                                        <span class="text-brown">Showing</span>
                                        <span class="font-weight-bolder text-dark"
                                            ng-bind="dataFrom ? dataFrom : 0"></span>
                                        <span class="text-brown">to</span>
                                        <span class="font-weight-bolder text-dark" ng-bind="dataTo ? dataTo : 0"></span>
                                        <span class="text-brown">of</span>
                                        <span class="font-weight-bolder text-dark" ng-bind="totalData"></span>
                                        <span class="text-brown">Results</span>
                                    </div>
                                    <div class="pt-2 font-weight-bolder text-dark hide-on-load"
                                        ng-class="{show: !totalData}" ng-bind="'No Results'"></div>
                                    <div>
                                        <a href="javascript:void(0)"
                                            class="btn bg-dark mb-0 px-3 global-filter-toggle-btn mt-1 rounded-0 text-white"
                                            type="button" ng-click="globaltogglefilters()" ng-class="{show: 1}">
                                            <i ng-show="!globalFiltersToggle" class="fa fa-search fa-2x"></i>
                                            <i ng-show="globalFiltersToggle" class="fa fa-times fa-2x"></i>
                                        </a>
                                        <a href="javascript:void(0)"
                                            class="btn bg-info mb-0 mt-1 rounded-0 filter-toggle-btn text-white"
                                            type="button" ng-click="togglefilters()" ng-class="{show: !filtersToggle}"
                                            ng-bind="'Show Filters'"></a>
                                        <a href="javascript:void(0)"
                                            class="btn bg-warning mb-0 mt-1 rounded-0 filter-toggle-btn text-white"
                                            type="button" ng-click="togglefilters()" ng-class="{show: filtersToggle}"
                                            ng-bind="'Hide Filters'"></a>
                                    </div>
                                </div>

                                <div class="table-responsive p-0 border-top">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-info text-xs font-weight-bolder opacity-7">
                                                    Sno
                                                </th>
                                                <th class="text-left text-uppercase text-info text-xs font-weight-bolder opacity-7"
                                                    ng-click="sortByField('product.product_name')">
                                                    Package Name <i class="fa fa-sort" aria-hidden="true"></i>
                                                </th>
                                                <th class="text-left text-uppercase text-info text-xs font-weight-bolder opacity-7"
                                                    ng-click="sortByField('license_type')">
                                                    License Type <i class="fa fa-sort" aria-hidden="true"></i>
                                                </th>
                                                <th class="text-left text-uppercase text-info text-xs font-weight-bolder opacity-7"
                                                    ng-click="sortByField('license_key')">
                                                    License Key <i class="fa fa-sort" aria-hidden="true"></i>
                                                </th>

                                                <th class="text-left text-uppercase text-info text-xs font-weight-bolder opacity-7"
                                                    ng-click="sortByField('customer.email')">
                                                    Customer Email <i class="fa fa-sort" aria-hidden="true"></i>
                                                </th>
                                                <th class="text-left text-uppercase text-info text-xs font-weight-bolder opacity-7"
                                                    ng-click="sortByField('status')">
                                                    Status <i class="fa fa-sort" aria-hidden="true"></i>
                                                </th>

                                                <th
                                                    class="text-left text-uppercase text-info text-xs font-weight-bolder opacity-7">
                                                    Action
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            <tr class="global-search-row" ng-class="{show: globalFiltersToggle}">
                                                <td colspan="4"></td>
                                                <td colspan="3">
                                                    <input type="text" class="form-control"
                                                        ng-model="licenseGlobalSearch" ng-change="callPaginateData()"
                                                        placeholder="Search Here...">
                                                </td>
                                            </tr>

                                            <tr class="filter-row" ng-class="{show: filtersToggle}">
                                                <td></td>
                                                <td>
                                                    <input type="text" class="form-control"
                                                        ng-model="productNameFilter" ng-change="callPaginateData()"
                                                        placeholder="Search Here...">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control"
                                                        ng-model="licenseTypeFilter" ng-change="callPaginateData()"
                                                        placeholder="Search Here...">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" ng-model="licenseFilter"
                                                        ng-change="callPaginateData()" placeholder="Search Here...">
                                                </td>
                                                <!--<td>
                                                                <input type="text" class="form-control" ng-model="macAddressFilter" ng-change="callPaginateData()" placeholder="Search Here...">
                                                            </td>
                                                            <td>
                                                                <input type="text" class="form-control" ng-model="orderReferenceNoFilter" ng-change="callPaginateData()" placeholder="Search Here...">
                                                            </td>
                                                            <td>
                                                                <input type="text" class="form-control" ng-model="orderSourceFilter" ng-change="callPaginateData()" placeholder="Search Here...">
                                                            </td>
                                                            -->
                                                <td>
                                                    <input type="text" class="form-control" ng-model="emailFilter"
                                                        ng-change="callPaginateData()" placeholder="Search Here...">
                                                </td>
                                                <td>
                                                    <select class="form-control" ng-model="statusFilter"
                                                        ng-change="getPaginateData()">
                                                        <option value=""> -- Status -- </option>
                                                        <option value="AVAILABLE"> Available </option>
                                                        <option value="PURCHASED"> Activated </option>
                                                        <option value="DEACTIVATED"> Deactivated </option>
                                                    </select>
                                                </td>
                                                <!-- <td>
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
                                                            <td></td> -->
                                                <td></td>
                                            </tr>

                                            <tr class="data-row"
                                                ng-repeat="license in licenselist | orderBy: sortBy : reverse"
                                                ng-class="{show: licenselist}">
                                                <td class="ps-4">
                                                    <p class="text-xs font-weight-bold mb-0"
                                                        ng-bind="($index + dataFrom)"></p>
                                                </td>
                                                <td class="ps-4">
                                                    <p class="text-xs font-weight-bold mb-0">
                                                        <a href="javascript:void(0);" class="me-1"
                                                            data-bs-toggle="modal" data-bs-target="#LicenseProductModal"
                                                            ng-click="getLicenseProduct(license.license_key)">
                                                            <b>
                                                                [[license.package.package_name]]
                                                            </b>
                                                        </a>
                                                    </p>
                                                </td>
                                                <td class="ps-4">
                                                    <p class="text-xs font-weight-bold mb-0"
                                                        ng-bind="license.license_type | textCapitalize"></p>
                                                </td>
                                                <td class="text-nowrap">
                                                    <span class="text-xs font-weight-bold mb-0 me-1"
                                                        ng-bind="license.license_key || license.hashed_license_key"
                                                        id="LicenseKey-field-[[ ($index + 1) ]]"></span>
                                                    <i class="fa fa-eye text-info cursor-pointer" data-bs-toggle="modal"
                                                        data-bs-target="#showLicenseKeyModal" title="Reveal License Key"
                                                        ng-click="getLicenseId(license, ($index + 1)); clearFormData('showLicenseForm')"
                                                        ng-show="!['SUPER_ADMIN', 'ADMIN'].includes(authenticatedUser.user_type)"></i>
                                                    <i class="fas fa-copy text-primary copy-text cursor-pointer d-none"
                                                        ng-click="copyToClipboard($event.target, 'LicenseKey-field-' + ($index + 1))"
                                                        title="Copy"></i>
                                                </td>
                                                <!--
                                                            <td class="ps-4 text-nowrap">
                                                                <span class="text-xs font-weight-bold me-1" ng-bind="license.mac_address"></span>
                                                                <i class="fas fa-eraser text-danger cursor-pointer" data-bs-toggle="modal" data-bs-target="#alertModal" ng-click="getLicenseId(license, ($index + 1), 'EmptyMACAlert')" ng-show="license.mac_address" title="Reset MAC Address"></i>
                                                            </td>
                                                            <td class="ps-4">
                                                                <span class="text-xs font-weight-bold" ng-bind="license.order.order_reference_no"></span>
                                                            </td>
                                                            <td class="ps-4">
                                                                <span class="text-xs font-weight-bold" ng-bind="license.order.order_source"></span>
                                                            </td>
                                                            -->
                                                <td class="ps-4">
                                                    <span class="text-xs font-weight-bold"
                                                        ng-bind="license.customer.email"></span>
                                                </td>
                                                <td class="ps-4">
                                                    <p class="text-sm font-weight-bold text-capitalize mb-0">
                                                        <span class="badge bg-gradient-warning status-badge-two"
                                                            ng-if="license.status == 'AVAILABLE'"
                                                            ng-bind="license.status | textCapitalize"></span>
                                                        <span class="badge bg-gradient-success status-badge-two"
                                                            ng-if="license.status == 'PURCHASED'"
                                                            ng-bind="'Activated' | textCapitalize"></span>
                                                        <span class="badge bg-gradient-danger status-badge-two"
                                                            ng-if="license.status == 'EXPIRED'"
                                                            ng-bind="license.status | textCapitalize"></span>
                                                        <span class="badge bg-gradient-danger status-badge-two"
                                                            ng-if="license.status == 'DEACTIVATED'"
                                                            ng-bind="license.status | textCapitalize"></span>
                                                    </p>
                                                </td>
                                                <!-- <td class="ps-4 text-nowrap">
                                                                <span class="text-secondary text-xs font-weight-bold" ng-bind="license.purchased_date | strToDate | date : 'dd-MM-y'"></span>
                                                            </td>
                                                            <td class="ps-4 text-nowrap">
                                                                <span class="text-secondary text-xs font-weight-bold" ng-bind="license.expiry_date | strToDate | date : 'dd-MM-y'"></span>
                                                            </td>
                                                            <td class="ps-4 text-nowrap">
                                                                <span class="text-secondary text-xs font-weight-bold" ng-bind="license.created_at | strToDate | date : 'dd-MM-y hh:mm:ss a'"></span>
                                                            </td> -->
                                                <td class="text-center">
                                                    <!-- <a href="javascript:void(0);" class="me-1" data-bs-toggle="modal" data-bs-target="#updateModal" ng-if="license.status != 'EXPIRED' && license.status != 'DEACTIVATED'" ng-click="getCustomerDetails(license.id)" title="Update">
                                                                    <i class="fas fa-edit text-info"></i>
                                                                </a> -->
                                                    <div class="col">
                                                        <a href="javascript:void(0);" class="me-1"
                                                            data-bs-toggle="modal" data-bs-target="#licenseDetailModal"
                                                            ng-click="getlicenseDetails(license.license_uuid)"
                                                            title="License Details">
                                                            <i class="fas fa-eye text-info"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" class="me-1"
                                                            data-bs-toggle="modal" data-bs-target="#LicenseHistoryModal"
                                                            ng-click="getLicenseHistory(license.license_uuid)"
                                                            title="License History">
                                                            <i class="fas fa-history text-info"></i>
                                                        </a>
                                                    </div>

                                                    <div class="col">
                                                        <a href="javascript:void(0);" class="me-1"
                                                            ng-class="{ 'empty-div': license.status !== 'PURCHASED' }"
                                                            ng-if="license.status == 'PURCHASED' && license.status != 'DEACTIVATED'"
                                                            data-bs-toggle="modal" data-bs-target="#alertModal"
                                                            ng-click="getLicenseId(license, '', 'DeactivateAlert');"
                                                            title="Deactivate License">
                                                            <i class="fas fa-ban text-warning"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" class="me-1"
                                                            ng-if="license.status == 'DEACTIVATED'" data-bs-toggle="modal"
                                                            data-bs-target="#alertModal"
                                                            ng-click="getLicenseId(license, '', 'ActivateAlert');"
                                                            title="Activate License">
                                                            <i class="fa fa-hand-o-right text-success"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" class="me-1"
                                                            data-bs-toggle="modal" data-bs-target="#alertModal"
                                                            ng-click="getLicenseId(license, '', 'DeleteAlert');"
                                                            title="Delete License">
                                                            <i class="fas fa-trash text-danger"></i>
                                                        </a>
                                                    </div>

                                                    <div class="col">
                                                        <a href="javascript:void(0);" class="me-1"
                                                            data-bs-toggle="modal" data-bs-target="#renewModal"
                                                            ng-click="getRenewData(license, license.product); getLicenseCodes();"
                                                            ng-show="['PURCHASED', 'EXPIRED'].includes(license.status)"
                                                            title="Renew License">
                                                            <i class="fa fa-refresh text-primary"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" class="me-1"
                                                            data-bs-toggle="modal" data-bs-target="#alertModal"
                                                            ng-click="getLicenseId(license, '', 'ResetMACAlert')"
                                                            ng-show="license.status == 'PURCHASED'"
                                                            title="Reset Mac License">
                                                            <i class="fas fa-eraser text-danger"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr class="no-data-found-row" ng-class="{show: licenselist.length == 0}">
                                                <td colspan="7" class="text-center text-secondary">No Data Found.</td>
                                            </tr>

                                        </tbody>
                                    </table>

                                    <!-- Pagination -->
                                    <div class="mt-5 pagination-container" ng-class="{show: licenselist.length > 0}">
                                        <pagination></pagination>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Generate License Modal -->
        <div class="modal fade" id="generateLicenseModal" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-info">
                        <h5 class="modal-title text-white" id="generateLicenseModalLabel">Generate License Key</h5>
                        <button type="button" class="btn-close"data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"></span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <form name="generateLicenseForm" method="post" ng-submit="generateLicense()">
                            @csrf
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="productCode"> Product Code <span class="text-warning">*</span> </label>
                                    <select id="productCode" name="productCode" class="form-control"
                                        ng-model="productCode" required>
                                        <option value=""> -- Select -- </option>
                                        <optgroup label="Packages">
                                            <option ng-repeat="package in packages"
                                                value='[[ {"type": "PACKAGE", "value": package.package_code} ]]'>[[
                                                package.package_name ]]</option>
                                        </optgroup>
                                        <optgroup label="Product Codes">
                                            <option ng-repeat="productCode in productCodes"
                                                value='[[ {"type": "PRODUCT_CODE", "value": productCode.product_code} ]]'>
                                                [[ productCode.product_name ]]</option>
                                        </optgroup>
                                    </select>
                                    <div class="text-danger mt-1 text-xs"
                                        ng-show="generateLicenseForm.productCode.$touched && generateLicenseForm.productCode.$invalid">
                                        <span ng-show="generateLicenseForm.productCode.$error.required">* The product code
                                            field is required.</span>
                                    </div>
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <label for="licenseCode"> License Code <span class="text-warning">*</span> </label>
                                    <select id="licenseCode" name="licenseCode" class="form-control"
                                        ng-model="licenseCode" required>
                                        <option value=""> -- Select License Code -- </option>
                                        <option ng-repeat="licenseCode in licenseCodes" value="[[ licenseCode.code ]]">[[
                                            licenseCode.name ]]</option>
                                    </select>

                                    <div class="text-danger mt-1 text-xs"
                                        ng-show="generateLicenseForm.licenseCode.$touched && generateLicenseForm.licenseCode.$invalid">
                                        <span ng-show="generateLicenseForm.licenseCode.$error.required">* The license code
                                            field is required.</span>
                                    </div>
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <label for="counts"> Counts </label>
                                    <input id="counts" type="number" name="counts" autocomplete="off"
                                        min="1" max="20" class="form-control" placeholder="Counts"
                                        ng-model="counts">

                                    <div class="text-danger mt-1 text-xs"
                                        ng-show="generateLicenseForm.counts.$touched && generateLicenseForm.counts.$invalid">
                                        <span ng-show="generateLicenseForm.counts.$error.required">* The counts field is
                                            required.</span>
                                    </div>
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <label for="orderSource"> Order Source </label>
                                    <select class="form-control" ng-model="orderSource" name="orderSource"
                                        id="orderSource">
                                        <option value=""> -- Select Order Source -- </option>
                                        <option value="LinkedIn"> LinkedIn </option>
                                        <option value="Mail"> Mail </option>
                                        <option value="Phone"> Phone </option>
                                        <option value="Client"> Client </option>
                                        <option value="Other"> Other </option>
                                    </select>

                                    <div class="text-danger mt-1 text-xs"
                                        ng-show="generateLicenseForm.orderSource.$touched && generateLicenseForm.orderSource.$invalid">
                                        <span ng-show="generateLicenseForm.orderSource.$error.required">* The order source
                                            field is required.</span>
                                    </div>
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <label for="orderReferenceNo"> Order Reference No </label>
                                    <input id="orderReferenceNo" type="text" name="orderReferenceNo"
                                        class="form-control" placeholder="Order Reference No"
                                        ng-model="orderReferenceNo">


                                    <div class="text-danger mt-1 text-xs"
                                        ng-show="generateLicenseForm.orderReferenceNo.$touched && generateLicenseForm.orderReferenceNo.$invalid">
                                        <span ng-show="generateLicenseForm.orderReferenceNo.$error.required">* The order
                                            reference no. field is required.</span>
                                    </div>
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <label for="orderInfo"> Order Info </label>
                                    <input id="orderInfo" type="text" name="orderInfo" class="form-control"
                                        placeholder="Order Info" ng-model="orderInfo">

                                    <div class="text-danger mt-1 text-xs"
                                        ng-show="generateLicenseForm.orderInfo.$touched && generateLicenseForm.orderInfo.$invalid">
                                        <span ng-show="generateLicenseForm.orderInfo.$error.required">* The order info
                                            field is required.</span>
                                    </div>
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <label for="orderTime"> Order Time </label>
                                    <datepicker date-format="dd-MM-yyyy" date-min-limit="[[ yesterdayDate ]]">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="orderTime"
                                                ng-model="orderTime" placeholder="Order Time" autocomplete="off">
                                            <span class="input-group-text" style="cursor: pointer">
                                                <i class="fa fa-lg fa-calendar"></i>
                                            </span>
                                        </div>
                                    </datepicker>

                                    <div class="text-danger mt-1 text-xs"
                                        ng-show="generateLicenseForm.orderTime.$touched && generateLicenseForm.orderTime.$invalid">
                                        <span ng-show="generateLicenseForm.orderTime.$error.required">* The order time
                                            field is required.</span>
                                    </div>
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <label for="email"> Email </label>
                                    <input id="email" type="email" name="email" class="form-control"
                                        placeholder="email" ng-model="email">

                                    <div class="text-danger mt-1 text-xs"
                                        ng-show="generateLicenseForm.email.$touched && generateLicenseForm.email.$invalid">
                                        <span ng-show="generateLicenseForm.email.$error.required">* The email field is
                                            required.</span>
                                        <span ng-show="generateLicenseForm.email.$error.email">* The email must be a valid
                                            email address.</span>
                                    </div>
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <label for="firstName"> Fist Name </label>
                                    <input id="firstName" type="text" name="firstName" class="form-control"
                                        placeholder="first name" ng-model="firstName">

                                    <div class="text-danger mt-1 text-xs"
                                        ng-show="generateLicenseForm.firstName.$touched && generateLicenseForm.firstName.$invalid">
                                        <span ng-show="generateLicenseForm.firstName.$error.required">* The first name
                                            field is required.</span>
                                    </div>
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <label for="lastName"> Last Name </label>
                                    <input id="lastName" type="text" name="lastName" class="form-control"
                                        placeholder="last name" ng-model="lastName">

                                    <div class="text-danger mt-1 text-xs"
                                        ng-show="generateLicenseForm.lastName.$touched && generateLicenseForm.lastName.$invalid">
                                        <span ng-show="generateLicenseForm.lastName.$error.required">* The last name field
                                            is required.</span>
                                    </div>
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <label for="phoneNo"> Phone No. </label>
                                    <input id="phoneNo" type="text" name="phoneNo" class="form-control"
                                        placeholder="Phone No." maxlength="30" ng-model="phoneNo">

                                    <div class="text-danger mt-1 text-xs"
                                        ng-show="generateLicenseForm.phoneNo.$touched && generateLicenseForm.phoneNo.$invalid">
                                        <span ng-show="generateLicenseForm.phoneNo.$error.required">* The phone no. field
                                            is required.</span>
                                    </div>
                                </div>

                                <div class="text-danger mt-4 mb-2" id="generateLicense-error-res"></div>

                                <div class="col-md-12 mt-2 pt-4 border-top text-right">
                                    <button type="submit" class="btn bg-gradient-warning"> Generate </button>
                                </div>
                            </div>
                        </form>
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
                                <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info"
                                    ng-bind="licenseDetail.license_key || licenseDetail.hashed_license_key"></div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-secondary mb-1"> License Type :</div>
                                <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info"
                                    ng-bind="licenseDetail.license_type | textCapitalize"></div>
                            </div>
                            <div class="col-md-4"  ng-show="activeTab === 'PACKAGE'">
                                <div class="text-secondary mb-1"> Package Name :</div>
                                <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info"
                                    ng-bind="licenseDetail.package.package_name"></div>
                            </div>
                            <div class="col-md-4" ng-class="{'mt-3' : activeTab === 'PACKAGE'}" ng-show="activeTab === 'PRODUCT'">
                                <div class="text-secondary mb-1"> Product Name :</div>
                                <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info"
                                    ng-bind="licenseDetail.product.product_name"></div>
                            </div>
                            <div class="col-md-4 mt-3" ng-show="activeTab === 'PRODUCT'">
                                <div class="text-secondary mb-1"> Product ID :</div>
                                <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info"
                                    ng-bind="licenseDetail.product.product_id"></div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="text-secondary mb-1"> MAC Address :</div>
                                <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info">
                                    [[ licenseDetail.mac_address ]] <i class="fas fa-eraser text-danger cursor-pointer"
                                        data-bs-toggle="modal" data-bs-target="#alertModal"
                                        ng-click="getLicenseId(licenseDetail, ($index + 1), 'ResetMACAlert')"
                                        ng-show="licenseDetail.mac_address" title="Reset MAC Address"></i>
                                </div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="text-secondary mb-1"> Created On :</div>
                                <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info"
                                    ng-bind="licenseDetail.created_at | strToDate | date : 'dd-MM-y hh:mm:ss a'"></div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="text-secondary mb-1"> Activation On :</div>
                                <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info"
                                    ng-bind="licenseDetail.purchased_date | strToDate | date : 'dd-MM-y'"></div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="text-secondary mb-1"> Expiry On :</div>
                                <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info"
                                    ng-bind="licenseDetail.expiry_date | strToDate | date : 'dd-MM-y'"></div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="text-secondary mb-1"> Status :</div>
                                <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info"
                                    ng-bind="(licenseDetail.status == 'PURCHASED') ? 'Activated' : licenseDetail.status | textCapitalize">
                                </div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="text-secondary mb-1"> Order Source :</div>
                                <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info"
                                    ng-bind="licenseDetail.order.order_source"></div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="text-secondary mb-1"> Order Reference No :</div>
                                <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info"
                                    ng-bind="licenseDetail.order.order_reference_no"></div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="text-secondary mb-1"> Order Info :</div>
                                <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info"
                                    ng-bind="licenseDetail.order.order_info"></div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="text-secondary mb-1"> Order Time :</div>
                                <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info"
                                    ng-bind="licenseDetail.order.order_placed_at | strToDate | date : 'dd-MM-y'"></div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="text-secondary mb-1"> Customer Name :</div>
                                <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info"
                                    ng-bind="(licenseDetail.customer.last_name) ? licenseDetail.customer.first_name + ' ' + licenseDetail.customer.last_name : licenseDetail.customer.first_name">
                                </div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="text-secondary mb-1"> Customer Email :</div>
                                <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info"
                                    ng-bind="licenseDetail.customer.email"></div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="text-secondary mb-1"> Customer Phone :</div>
                                <div class="bg-white rounded text-dark px-2 py-2 ps-3 mh-42 border-bottom border-info"
                                    ng-bind="licenseDetail.customer.phone"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Modal -->
        <div class="modal fade" id="updateModal" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-info">
                        <h5 class="modal-title text-white" id="updateModalLabel">Update Customer Details</h5>
                        <button type="button" class="btn-close"data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"></span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <form name="updateForm" method="post" ng-submit="updateCustomerDetails()">
                            @csrf
                            <div class="text-danger mb-2" id="updateCustomer-error-res"></div>
                            <input id="updateLicenseKey" type="hidden" name="updateLicenseKey" class="form-control"
                                ng-model="updateLicenseKey">
                            <div class="row">
                                <div class="col-lg-6 mb-3">
                                    <label for="productCode"> MAC Address </label>
                                    <input id="updateMacAddress" type="text" name="updateMacAddress"
                                        class="form-control" placeholder="MAC Address" ng-model="updateMacAddress">
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <label for="licenseCode"> Email </label>
                                    <input id="updateEmail" type="text" name="updateEmail" class="form-control"
                                        placeholder="email" ng-model="updateEmail">

                                    <div class="text-danger mt-1 text-xs"
                                        ng-show="updateForm.updateEmail.$touched && updateForm.updateEmail.$invalid">
                                        <span ng-show="updateForm.licenseCode.$error.required">* The updateEmail field is
                                            required.</span>
                                        <span ng-show="updateForm.updateEmail.$error.email">* The email must be a valid
                                            email address.</span>
                                    </div>
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <label for="counts"> Fist Name </label>
                                    <input id="updateFirstName" type="text" name="updateFirstName"
                                        class="form-control" placeholder="first name" ng-model="updateFirstName">

                                    <div class="text-danger mt-1 text-xs" ng-show="updateForm.updateFirstName.$touched">
                                        <span ng-show="updateForm.updateFirstName.$error.required">* The first name field
                                            is required.</span>
                                    </div>
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <label for="counts"> Last Name </label>
                                    <input id="updateLastName" type="text" name="updateLastName" class="form-control"
                                        placeholder="last name" ng-model="updateLastName">

                                    <div class="text-danger mt-1 text-xs" ng-show="updateForm.updateLastName.$touched">
                                        <span ng-show="updateForm.updateLastName.$error.required">* The last name field is
                                            required.</span>
                                    </div>
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <label for="counts"> Phone No. </label>
                                    <input id="updatePhoneNo" type="text" name="updatePhoneNo" class="form-control"
                                        placeholder="Phone No." maxlength="30" ng-model="updatePhoneNo">

                                    <div class="text-danger mt-1 text-xs" ng-show="updateForm.updatePhoneNo.$touched">
                                        <span ng-show="updateForm.updatePhoneNo.$error.required">* The phone no. field is
                                            required.</span>
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

                        <form name="renewForm" method="post" ng-submit="renewLicenseDetails()">
                            @csrf
                            <div class="text-danger mb-2" id="renewLicense-error-res"></div>
                            <input id="renewLicenseId" type="hidden" ng-model="renewLicenseId">
                            <input id="renewalType" type="hidden" ng-model="renewalType">
                            <div class="row">
                                <div class="col-lg-6 mb-3">
                                    <label for="renewLicense"> License Key <span class="text-warning">*</span> </label>
                                    <input id="renewLicense" type="text" name="renewLicense" class="form-control"
                                        placeholder="License Key" ng-model="renewLicense" readonly>
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <label for="renewProductCode"> Product Code <span class="text-warning">*</span>
                                    </label>
                                    <input id="renewProductCode" type="text" name="renewProductCode"
                                        class="form-control" placeholder="Product Code" ng-model="renewProductCode"
                                        readonly>
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <label for="renewLicenseCode"> License Code <span class="text-warning">*</span>
                                    </label>
                                    <select id="renewLicenseCode" name="renewLicenseCode" class="form-control"
                                        ng-model="renewLicenseCode" required>
                                        <option value=""> -- Select License Code -- </option>
                                        <option ng-repeat="licenseCode in licenseCodes" value="[[ licenseCode.code ]]">
                                            [[ licenseCode.name ]]</option>
                                    </select>

                                    <div class="text-danger mt-1 text-xs"
                                        ng-show="renewForm.renewLicenseCode.$touched && renewForm.renewLicenseCode.$invalid">
                                        <span ng-show="renewForm.renewLicenseCode.$error.required">* The license code
                                            field is required.</span>
                                    </div>
                                </div>

                                {{-- <div class="col-lg-6 mb-3" ng-show="renewPackageFlag">
                                    <label> Renewal For <span class="text-warning">*</span> </label>
                                    <div>
                                        <input id="productType" type="radio" name="renewalType"
                                            ng-model="renewalType" value="PRODUCT_CODE"
                                            ng-required="renewPackageFlag"> <label for="productType">Product
                                            Only</label>
                                        <input id="packageType" type="radio" name="renewalType" class="ms-2"
                                            ng-model="renewalType" value="PACKAGE" ng-required="renewPackageFlag">
                                        <label for="packageType">Package</label>
                                    </div>
                                </div> --}}

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
                        <div class="text-dark mb-2" ng-show="alertType == 'EmptyMACAlert'">
                            You’re about to clear the MAC Address. This action leads to revoke the access to the user
                            device. Are you sure?
                        </div>

                        <div class="text-dark mb-2" ng-show="alertType == 'DeactivateAlert'">
                            You’re about to deactivate the license key. Are you sure?
                            <form name="deactivateLicenseForm" method="post" ng-submit="deactivateLicense()">
                                <div class="col-lg-6 mb-3 mt-3">
                                    @csrf
                                    <input type="hidden" name="licenseId" ng-model="licenseId">
                                    <input type="hidden" name="deactivateType" ng-model="deactivateType">
                                    {{-- <label> Deactivate <span class="text-warning">*</span> </label>
                                    <div>
                                        <input id="deactivateProductType" type="radio" name="deactivateType"
                                            ng-model="deactivateType" value="PRODUCT_CODE" ng-required="packageFlag">
                                        <label for="deactivateProductType">Product Only</label>
                                        <input id="deactivatePackageType" type="radio" name="deactivateType"
                                            class="ms-2" ng-model="deactivateType" value="PACKAGE"
                                            ng-required="packageFlag"> <label
                                            for="deactivatePackageType">Package</label>
                                    </div> --}}
                                </div>

                                <div class="col-md-12 mt-4 pt-4 border-top text-right">
                                    <button type="submit" class="btn bg-gradient-success"> Yes </button>
                                    <button type="button" class="btn bg-gradient-warning" data-bs-dismiss="modal"> No
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="text-dark mb-2" ng-show="alertType == 'ResetMACAlert'">
                            You’re about to clear the MAC Address. This action leads to revoke the access to the user
                            device. Are you sure?
                            <form name="resetMacForm" method="post" ng-submit="resetMAC()">
                                <div class="col-lg-6 mb-3 mt-3">
                                    @csrf
                                    <input type="hidden" name="licenseId" ng-model="licenseId" />
                                    <input type="hidden" name="resetType" ng-model="resetType" />
                                    {{-- <label> Deactivate <span class="text-warning">*</span> </label>
                                    <div>
                                        <input id="resetProductType" type="radio" name="resetType"
                                            ng-model="resetType" value="PRODUCT" ng-required="packageFlag"> <label
                                            for="resetProductType">Product Only</label>
                                        <input id="resetPackageType" type="radio" name="resetType" class="ms-2"
                                            ng-model="resetType" value="PACKAGE" ng-required="packageFlag"> <label
                                            for="resetPackageType">Package</label>
                                    </div> --}}
                                </div>
                                <div class="col-md-12 mt-4 pt-4 border-top text-right">
                                    <button type="submit" class="btn bg-gradient-success"> Yes </button>
                                    <button type="button" class="btn bg-gradient-warning" data-bs-dismiss="modal"> No
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="text-dark mb-2" ng-show="alertType == 'ActivateAlert'">
                            You’re about to activate the license key. Are you sure?

                            <form name="activateLicenseForm" method="post" ng-submit="activateLicense()">
                                <div class="col-lg-6 mb-3 mt-3" ng-show="packageFlag">
                                    @csrf
                                    <input type="hidden" name="licenseId" ng-model="licenseId" />
                                    <input type="hidden" name="activateType" ng-model="activateType" />
                                    {{-- <label> Activate <span class="text-warning">*</span> </label>
                                    <div>
                                        <input id="activateProductType" type="radio" name="activateType"
                                            ng-model="activateType" value="PRODUCT_CODE" ng-required="packageFlag">
                                        <label for="activateProductType">Product Only</label>
                                        <input id="activatePackageType" type="radio" name="activateType"
                                            class="ms-2" ng-model="activateType" value="PACKAGE"
                                            ng-required="packageFlag"> <label for="activatePackageType">Package</label>
                                    </div> --}}
                                </div>

                                <div class="col-md-12 mt-4 pt-4 border-top text-right">
                                    <button type="submit" class="btn bg-gradient-success"> Yes </button>
                                    <button type="button" class="btn bg-gradient-warning" data-bs-dismiss="modal"> No
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="text-dark mb-0" ng-show="alertType == 'DeleteAlert'">
                            You’re about to delete the license key ([[ licenseData.hashed_license_key ]]) with the product
                            ([[ licenseData.product.product_name ]]). To confirm, enter your password.

                            <form name="deleteLicenseForm" method="post" ng-submit="deleteLicenseKey()">
                                @csrf
                                <div class="text-danger mt-3" id="deleteLicenseKey-error-res"></div>
                                <div class="row">
                                    <input type="hidden" name="licenseId" ng-model="licenseId">
                                    <input type="hidden" name="deleteType" ng-model="deleteType">
                                    <div ng-class="{'col-lg-12': packageFlag}" class="mb-3">
                                        <label> Password <span class="text-warning">*</span> </label>
                                        <div class="input-group">
                                            <input id="userPassword"
                                                type="[[ (userPasswordToggle == true) ? 'password' : 'text' ]]"
                                                name="userPassword" class="form-control" placeholder="Password"
                                                ng-model="userPassword" ng-change="enterValue()"
                                                aria-describedby="input-addon" required>
                                            <span class="input-group-text" id="input-addon"><i
                                                    class="fa fa-eye cursor-pointer"
                                                    ng-click="toggleUserPassword()"></i></span>
                                        </div>

                                        <div class="text-danger mt-1 text-xs"
                                            ng-show="deleteLicenseForm.userPassword.$touched && deleteLicenseForm.userPassword.$invalid">
                                            <span ng-show="deleteLicenseForm.userPassword.$error.required">The password
                                                field is required.</span>
                                        </div>
                                    </div>

                                    {{-- <div class="col-lg-6 mb-3" ng-show="packageFlag">
                                        <label> Delete <span class="text-warning">*</span> </label>
                                        <div>
                                            <input id="deleteProductType" type="radio" name="deleteType"
                                                ng-model="deleteType" value="PRODUCT_CODE" ng-required="packageFlag">
                                            <label for="deleteProductType">Product Only</label>
                                            <input id="deletePackageType" type="radio" name="deleteType"
                                                class="ms-2" ng-model="deleteType" value="PACKAGE"
                                                ng-required="packageFlag"> <label
                                                for="deletePackageType">Package</label>
                                        </div>
                                    </div> --}}

                                    <div class="col-md-12 mt-0 pt-4 border-top text-right">
                                        <button type="submit" class="btn bg-gradient-warning"> Submit </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="col-md-12 mt-4 pt-4 border-top text-right"
                            ng-show="alertType != 'DeleteAlert' && alertType != 'DeactivateAlert' && alertType != 'ActivateAlert' && alertType != 'ResetMACAlert'">
                            <button type="submit" class="btn bg-gradient-success"
                                ng-click="(alertType == 'EmptyMACAlert') ? resetMACAddress(licenseId) : (alertType == 'ActivateAlert') ? activateLicense(licenseId) : deactivateLicense(licenseId)">
                                Yes </button>
                            <button type="button" class="btn bg-gradient-warning" data-bs-dismiss="modal"> No
                            </button>
                        </div>

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

                        <form name="showLicenseForm" method="post" ng-submit="getActualLicenseKey()">
                            @csrf
                            <div class="text-danger mb-2" id="getActualLicenseKey-error-res"></div>
                            <div class="row">
                                <input type="hidden" name="licenseId" ng-model="licenseId">
                                <div class="col-lg-12 mb-3">
                                    <label for="password"> Password </label>
                                    <div class="input-group">
                                        <input id="password"
                                            type="[[ (passwordToggle == true) ? 'password' : 'text' ]]" name="password"
                                            class="form-control" placeholder="Password" ng-model="password"
                                            aria-describedby="input-addon" required>
                                        <span class="input-group-text" id="input-addon"><i
                                                class="fa fa-eye cursor-pointer" ng-click="togglePassword()"></i></span>
                                    </div>

                                    <div class="text-danger mt-1 text-xs"
                                        ng-show="showLicenseForm.password.$touched && showLicenseForm.password.$invalid">
                                        <span ng-show="showLicenseForm.password.$error.required">The password field is
                                            required.</span>
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
                                    <!-- <th class="text-uppercase text-info text-xs font-weight-bolder opacity-7">
                                                    Sno
                                                </th> -->
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7"
                                        ng-click="sortByHistoryField('entry_type')">
                                        Action <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7"
                                        ng-click="sortByHistoryField('license_key')">
                                        License Key <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7"
                                        ng-click="sortByHistoryField('mac_address')">
                                        Mac Address <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7"
                                        ng-click="sortByHistoryField('system_info')">
                                        System Info <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7"
                                        ng-click="sortByHistoryField('user_id')">
                                        Done By <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7"
                                        ng-click="sortByHistoryField('created_at')">
                                        Done On <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    ng-repeat="licenseHistory in licenseHistoryData | orderBy: sortByHistory: reverseHistory">
                                    <!-- <td class="ps-4" ng-bind="($index + 1)"></td> -->
                                    <td
                                        ng-bind="(licenseHistory.entry_type) ? licenseHistory.entry_type : 'MAC Address Update' | textCapitalize : (licenseHistory.entry_type == 'MAC_ADDRESS_UPDATE' || !licenseHistory.entry_type) ? 1 : ''">
                                    </td>
                                    <td class="ps-4" ng-bind="licenseHistory.hashed_license_key"></td>
                                    <td class="ps-4" ng-bind="licenseHistory.mac_address"></td>
                                    <td class="ps-4" ng-bind="licenseHistory.system_info | modifyJSON"></td>
                                    <td class="ps-4"
                                        ng-bind="(licenseHistory.user.last_name) ? licenseHistory.user.first_name + ' ' + licenseHistory.user.last_name: licenseHistory.user.first_name">
                                    </td>
                                    <td class="pe-4"
                                        ng-bind="licenseHistory.created_at | strToDate | date : 'dd-MM-y hh:mm:ss a'">
                                    </td>
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

        <!-- Generated License Keys Modal -->
        <span id="generatedLicenseKeysModalBtn" data-bs-toggle="modal"
            data-bs-target="#generatedLicenseKeysModal"></span>
        <div class="modal fade" id="generatedLicenseKeysModal" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-info">
                        <h5 class="modal-title text-white" id="generatedLicenseKeysLabel">Generated Licenses</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"></span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="text-dark mb-4"> The below-mentioned license key(s) are generated successfully.</div>
                        <div class="text-info mb-4" id="generatedLicenseKeys"></div>
                        <i class="fas fa-copy text-primary copy-text cursor-pointer"
                            ng-click="copyToClipboard($event.target, 'generatedLicenseKeys')" title="Copy"> Copy
                            License Key(s)</i>

                        <div class="col-md-12 mt-4 pt-4 border-top text-right">
                            <button type="button" class="btn bg-gradient-danger" data-bs-dismiss="modal"> Close
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="showSubLicenseKeyModal" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-info">
                        <h5 class="modal-title text-white" id="showSubLicenseKeyModalLabel">Reveal License Key</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"></span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <form name="showLicenseForm" method="post" ng-submit="getSubActualLicenseKey()">
                            @csrf
                            <div class="text-danger mb-2" id="getSubActualLicenseKey-error-res"></div>
                            <div class="row">
                                <input type="hidden" name="licenseId" ng-model="licenseId">
                                <div class="col-lg-12 mb-3">
                                    <label for="password"> Password </label>
                                    <div class="input-group">
                                        <input id="password"
                                            type="[[ (passwordToggle == true) ? 'password' : 'text' ]]" name="password"
                                            class="form-control" placeholder="Password" ng-model="password"
                                            aria-describedby="input-addon" required>
                                        <span class="input-group-text" id="input-addon"><i
                                                class="fa fa-eye cursor-pointer" ng-click="togglePassword()"></i></span>
                                    </div>

                                    <div class="text-danger mt-1 text-xs"
                                        ng-show="showLicenseForm.password.$touched && showLicenseForm.password.$invalid">
                                        <span ng-show="showLicenseForm.password.$error.required">The password field is
                                            required.</span>
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

        <!-- Product List -->
        <div class="modal fade" id="LicenseProductModal" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-info">
                        <h5 class="modal-title text-white" id="LicenseProductModal">Products</h5>
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
                                    <th class="text-left text-uppercase text-info text-xs font-weight-bolder opacity-7"
                                        ng-click="sortByProductField('product.product_name')">
                                        Product Name <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-left text-uppercase text-info text-xs font-weight-bolder opacity-7"
                                        ng-click="sortByProductField('license_type')">
                                        License Type <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-left text-uppercase text-info text-xs font-weight-bolder opacity-7"
                                        ng-click="sortByProductField('license_key')">
                                        License Key <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-left text-uppercase text-info text-xs font-weight-bolder opacity-7"
                                        ng-click="sortByProductField('customer.email')">
                                        Customer Email <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-left text-uppercase text-info text-xs font-weight-bolder opacity-7"
                                        ng-click="sortByProductField('status')">
                                        Status <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="data-row"
                                    ng-repeat="license in licenseProductData | orderBy: sortByProduct : reverseProduct"
                                    ng-class="{show: licenseProductData}">
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="($index + dataSubFrom)"></p>
                                    </td>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0">
                                            <b>
                                                [[license.product.product_name]]
                                            </b>
                                        </p>
                                    </td>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0"
                                            ng-bind="license.license_type | textCapitalize"></p>
                                    </td>
                                    <td class="text-nowrap">
                                        <span class="text-xs font-weight-bold mb-0 me-1"
                                            ng-bind="license.license_key || license.hashed_license_key"
                                            id="LicenseKey-field-sub-[[ ($index + 1) ]]"></span>
                                        <i class="fa fa-eye text-info cursor-pointer" data-bs-toggle="modal"
                                            data-bs-target="#showSubLicenseKeyModal" title="Reveal License Key"
                                            ng-show="!license.license_key"
                                            ng-click="getLicenseIdSub(license, ($index + 1)); clearFormData('showLicenseForm')"
                                        ></i>
                                        <i class="fas fa-copy text-primary copy-text cursor-pointer d-none"
                                            ng-click="copyToClipboard($event.target, 'LicenseKey-field-sub-' + ($index + 1))"
                                            title="Copy"></i>
                                    </td>
                                    <td class="ps-4">
                                        <span class="text-xs font-weight-bold" ng-bind="license.customer.email"></span>
                                    </td>
                                    <td class="ps-4">
                                        <p class="text-sm font-weight-bold text-capitalize mb-0">
                                            <span class="badge bg-gradient-warning status-badge-two"
                                                ng-if="license.status == 'AVAILABLE'"
                                                ng-bind="license.status | textCapitalize"></span>
                                            <span class="badge bg-gradient-success status-badge-two"
                                                ng-if="license.status == 'PURCHASED'"
                                                ng-bind="'Activated' | textCapitalize"></span>
                                            <span class="badge bg-gradient-danger status-badge-two"
                                                ng-if="license.status == 'EXPIRED'"
                                                ng-bind="license.status | textCapitalize"></span>
                                            <span class="badge bg-gradient-danger status-badge-two"
                                                ng-if="license.status == 'DEACTIVATED'"
                                                ng-bind="license.status | textCapitalize"></span>
                                        </p>
                                    </td>
                                </tr>

                                <tr class="no-data-found-row" ng-class="{show: licenseProductData.length == 0}">
                                    <td colspan="7" class="text-center text-secondary">No Data Found.</td>
                                </tr>

                            </tbody>
                        </table>
                        <div class="mt-5 pagination-container" ng-class="{show: licenseProductData.length > 0 && totalSubPages > 1}">
                            <subpagination></subpagination>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
