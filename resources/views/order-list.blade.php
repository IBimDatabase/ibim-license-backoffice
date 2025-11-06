@extends('layouts.main')
@section('content')
<div class="container-fluid py-4" ng-controller="orderController">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">All Orders</h5>
                        </div>
                        <!-- <a href="#" class="btn bg-gradient-primary mb-0" type="button" data-bs-toggle="modal" data-bs-target="#generateLicenseModal" ng-click="getCodes(); clearFormData('generateLicenseForm'); getYesterdayDate();"><i class="fa fa-plus-circle me-1"></i> Generate License Key</a> -->
                    </div>
                    <div class="px-0 pt-5">
                        <a href="javascript:void(0)" class="btn bg-gradient-info mb-0 hide-on-load" ng-class="{show: totalData}" ng-click="exportOrder()"><i class="fas fa-file-export me-1"></i> Export Excel</a>
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
                        <div>
                            <a href="javascript:void(0)" class="btn bg-info mb-0 mt-1 rounded-0 filter-toggle-btn text-white" type="button" ng-click="togglefilters()" ng-class="{show: !filtersToggle}" ng-bind="'Show Filters'"></a>
                            <a href="javascript:void(0)" class="btn bg-warning mb-0 mt-1 rounded-0 filter-toggle-btn text-white" type="button" ng-click="togglefilters()" ng-class="{show: filtersToggle}" ng-bind="'Hide Filters'"></a>
                        </div>
                    </div>

                    <div class="table-responsive p-0 border-top">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-info text-xs font-weight-bolder opacity-7">
                                        Sno
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByField('order_id')">
                                        Order ID <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByField('product_name')">
                                        Product/Package Name <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByField('license_type')">
                                        License Type <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByField('customer_email')">
                                        Customer Email <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByField('order_date')">
                                        Order Date <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7" ng-click="sortByField('order_status')">
                                        Order Status <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder opacity-7">
                                        Action <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>

                                <tr class="filter-row" ng-class="{show: filtersToggle}">
                                    <td></td>
                                    <td>
                                        <input type="text" class="form-control" ng-model="orderIdFilter" ng-change="callPaginateData()" placeholder="Search Here...">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" ng-model="productNameFilter" ng-change="callPaginateData()" placeholder="Search Here...">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" ng-model="licenseTypeFilter" ng-change="callPaginateData()" placeholder="Search Here...">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" ng-model="emailFilter" ng-change="callPaginateData()" placeholder="Search Here...">
                                    </td>
                                    <td>
                                        <datepicker date-format="dd-MM-yyyy">
                                            <input  type="text" class="form-control" ng-model="orderDateFilter" ng-change="callPaginateData()" placeholder="Search Here...">
                                        </datepicker>
                                    </td>
                                    <td>
                                        <select class="form-control" ng-model="statusFilter" ng-change="getPaginateData()">
                                            <option value=""> -- Status -- </option>
                                            <!--<option value="PROCESSING"> Processing </option>-->
                                            <option value="SUCCESS"> Success </option>
                                            <option value="FAILED"> Failed </option>
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

                                <tr class="data-row" ng-repeat="order in orders | orderBy: sortBy : reverse" ng-class="{show: orders}">
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="($index + dataFrom)"></p>
                                    </td>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="order.order_id"></p>
                                    </td>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="order.product.product_name"></p>
                                    </td>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="order.license_type.name | textCapitalize"></p>
                                    </td>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="order.customer.email"></p>
                                    </td>
                                    <td class="ps-4 text-nowrap">
                                        <span class="text-secondary text-xs font-weight-bold" ng-bind="order.order_date | strToDate | date : 'dd-MM-y'"></span>
                                    </td>
                                    <td class="ps-4">
                                        <p class="text-sm font-weight-bold text-capitalize mb-0">
                                            <!-- <span class="badge bg-gradient-warning" ng-if="order.order_status == 'PROCESSING'" ng-bind="order.order_status | textCapitalize"></span> -->
                                            <span class="badge bg-gradient-success status-badge-one" ng-if="order.order_status != 'FAILED'" ng-bind="'SUCCESS' | textCapitalize"></span>
                                            <span class="badge bg-gradient-danger status-badge-one" ng-if="order.order_status == 'FAILED'" ng-bind="order.order_status | textCapitalize"></span>
                                        </p>
                                    </td>
                                    <td class="text-center">                                        
                                       <a href="javascript:void(0);" class="mx-1" ng-show="order.order_status == 'FAILED'" title="Sync" data-bs-toggle="modal" data-bs-target="#alertModal" ng-click="getOrderId(order, '', 'SyncAlert')">
                                           <i class="cursor-pointer fa fa-refresh text-primary"></i>
                                       </a>
                                   </td>
                                </tr>

                                <tr class="no-data-found-row" ng-class="{show: !orders.length}">
                                    <td colspan="7" class="text-center text-secondary">No Data Found.</td>
                                </tr>
                                
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="mt-5 pagination-container" ng-class="{show: orders.length > 0}">
                            <pagination></pagination>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Modal -->
    <div class="modal fade" id="alertModal" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="spinner-overlay" ng-show="spinnerLoading">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Syncing...</span>
                    </div>
                </div>

                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white" id="alertLabel">Caution</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-dark mb-2" ng-show="alertType == 'DeleteAlert'">
                        You’re about to delete the order. This action won't reflect on the Woo-Commerce website. Are you sure?
                    </div>

                    <div class="text-dark mb-2" ng-show="alertType == 'SyncAlert'">
                        You’re about to sync the order from Woo-Commerce website. This action can't be undone. Are you sure?
                    </div>
                    
                    <div class="col-md-12 mt-4 pt-4 border-top text-right">
                        <button type="button" class="btn bg-gradient-success" ng-click="(alertType == 'DeleteAlert') ? deleteOrder(orderId) : syncOrder(orderId)"> Yes </button>
                        <button type="button" class="btn bg-gradient-warning" data-bs-dismiss="modal"> No </button>
                    </div>
                </div>
             </div>
        </div>
    </div>

</div>
@endsection
