@extends('layouts.main')
@section('content')
<div class="container-fluid py-4" ng-controller="productController">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">All Products</h5>
                        </div>
                        <div class="px-0">
                            <a href="javascript:void(0)" class="btn bg-gradient-info mb-0 d-none" type="button" data-bs-toggle="modal" data-bs-target="#importModal" ng-click="clearFormData('importProductForm')"><i class="fa fa-plus-circle me-1"></i> Import Excel</a>
                            <a href="javascript:void(0)" class="btn bg-gradient-primary mb-0 mx-2" type="button" data-bs-toggle="modal" data-bs-target="#addModal" ng-click="clearFormData('addProductForm')"><i class="fa fa-plus-circle me-1"></i> Add Product</a>
                            <!--</div>
                            <div class="px-0 pt-5">-->
                            <a href="javascript:void(0)" class="btn bg-gradient-info mb-0 hide-on-load" ng-class="{show: totalData}" ng-click="exportProduct()"><i class="fas fa-file-export me-1"></i> Export Excel</a>
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
                                    <th class="text-uppercase text-info text-xs font-weight-bolder">
                                        Sno
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder" ng-click="sortByField('product_name')">
                                        Product Name <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-uppercase text-info text-xs font-weight-bolder" ng-click="sortByField('product_code')">
                                        Product Code <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-uppercase text-info text-xs font-weight-bolder" ng-click="sortByField('product_number')">
                                        Product Id <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder" ng-click="sortByField('status')">
                                        Status <i class="fa fa-sort" aria-hidden="true"></i>
                                    </th>
                                    <th class="text-center text-uppercase text-info text-xs font-weight-bolder">
                                        Active License Count
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
                                        <input type="text" class="form-control" ng-model="productIdFilter" ng-change="callPaginateData()" placeholder="Search Here...">
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
                                    <!-- <td></td> -->
                                </tr>

                                <tr class="data-row" ng-repeat="product in products | orderBy: sortBy: reverse" ng-class="{show: products}">
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="($index + dataFrom)"></p>
                                    </td>
                                    
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="product.product_name"></p>
                                    </td>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="product.product_code"></p>
                                    </td>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="product.product_id"></p>
                                    </td>
                                    <td class="text-center">
                                        <p class="text-sm font-weight-bold text-capitalize mb-0">
                                            <span class="badge bg-gradient-success status-badge-one" ng-if="product.status == 'ACTIVE'" ng-bind="product.status | textCapitalize"></span>
                                            <span class="badge bg-gradient-danger status-badge-one" ng-if="product.status == 'INACTIVE'" ng-bind="product.status | textCapitalize"></span>
                                        </p>
                                    </td>
                                    <td class="ps-4 text-center">
                                        <p class="text-xs font-weight-bold mb-0" ng-bind="(activeLicenseCount[product.product_code]) ? activeLicenseCount[product.product_code] : '0'"></p>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <span class="text-secondary text-xs font-weight-bold" ng-bind="product.created_at | strToDate | date : 'dd-MM-y hh:mm:ss a'"></span>
                                    </td>
                                    <td class="text-center">
                                         <a href="javascript:void(0);" class="mx-1" ng-show="!product.wp_product_id" title="Update" data-bs-toggle="modal" data-bs-target="#updateModal" ng-click="getProductData(product)">
                                            <i class="fas fa-edit text-info"></i>
                                        </a> 
                                        <a href="javascript:void(0);" class="mx-1" ng-show="product.wp_product_id" title="Sync" data-bs-toggle="modal" data-bs-target="#alertModal" ng-click="getProductId(product, '', 'SyncAlert')">
                                            <i class="cursor-pointer fa fa-refresh text-primary"></i>
                                        </a>
                                        <a href="javascript:void(0);" class="mx-1" ng-show="!product.wp_product_id" title="Delete" data-bs-toggle="modal" data-bs-target="#alertModal" ng-click="getProductId(product, '', 'DeleteAlert')">
                                            <i class="cursor-pointer fas fa-trash text-danger"></i>
                                        </a>
                                    </td>
                                </tr>

                                <tr class="no-data-found-row" ng-class="{show: products.length == 0}">
                                    <td colspan="7" class="text-center text-secondary">No Data Found.</td>
                                </tr>
                            
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="mt-5 pagination-container" ng-class="{show: products.length > 0}">
                            <pagination></pagination>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addModal" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white" id="addModalLabel">Add Product</h5>
                    <button type="button" class="btn-close"data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    
                    <form name="addProductForm" method="post" ng-submit="addProduct()">
                        @csrf
                        <div class="text-danger mb-2 text-sm" id="addProduct-error-res"></div>
                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <label for="productName"> Product Name <span class="text-warning">*</span> </label>
                                <input id="productName" type="text" name="productName" class="form-control"
                                    placeholder="Product Name" ng-model="productName" ng-blur="generateProductCode(productName)" required>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="addProductForm.productName.$touched && addProductForm.productName.$invalid">
                                    <span ng-show="addProductForm.productName.$error.required">* The product name field is required.</span>
                                </div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="productCode"> Product Code <span class="text-warning">*</span> </label>
                                <input id="productCode" type="text" name="productCode" class="form-control"
                                    placeholder="Product Code" ng-model="productCode" required>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="addProductForm.productCode.$touched && addProductForm.productCode.$invalid">
                                    <span ng-show="addProductForm.productCode.$error.required">* The product code field is required.</span>
                                </div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="productPrefix"> Product Prefix <span class="text-warning">*</span> </label>
                                <input id="productPrefix" type="text" name="productPrefix" class="form-control"
                                    placeholder="Product Prefix" ng-model="productPrefix" ng-change="generateProductPrefix(productPrefix)" required>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="addProductForm.productPrefix.$touched && addProductForm.productPrefix.$invalid">
                                    <span ng-show="addProductForm.productPrefix.$error.required">* The product prefix field is required.</span>
                                </div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="description"> Description </label>
                                <textarea id="description" type="text" name="description" class="form-control"
                                    placeholder="Description" ng-model="description"></textarea>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="addProductForm.description.$touched && addProductForm.description.$invalid">
                                    <span ng-show="addProductForm.description.$error.required">* The description field is required.</span>
                                </div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="status"> Status <span class="text-warning">*</span> </label>
                                <select id="status" name="status" class="form-control" ng-model="status" required>
                                    <option value=""> -Select- </option>
                                    <option value="ACTIVE"> Active </option>
                                    <option value="INACTIVE"> Inactive </option>
                                </select>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="addProductForm.status.$touched && addProductForm.status.$invalid">
                                    <span ng-show="addProductForm.status.$error.required">* The status field is required.</span>
                                </div>
                            </div>   

                            <div class="text-sm mt-2">
                                <span class="text-dark font-weight-bolder"> Note: </span> 
                                <span class="text-warning ms-2"> * Product code can't be changed once created. </span>
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

    <!-- Update Modal -->
    <div class="modal fade" id="updateModal" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white" id="updateModalLabel">Update Product</h5>
                    <button type="button" class="btn-close"data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    
                    <form name="updateForm" method="post" ng-submit="updateProduct(productId)">
                        @csrf
                        <div class="text-danger mb-2" id="updateProduct-error-res"></div>
                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <label for="updateProductName"> Product Name <span class="text-warning">*</span> </label>
                                <input id="updateProductName" type="text" name="updateProductName" class="form-control"
                                    placeholder="Product Name" ng-model="updateProductName" required>
                                
                                <div class="text-danger mt-1 text-xs" ng-show="updateForm.updateProductName.$touched && updateForm.updateProductName.$invalid">
                                    <span ng-show="updateForm.updateProductName.$error.required">* The product name field is required.</span>
                                </div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="updateProductCode"> Product Code <span class="text-warning">*</span> </label>
                                <input id="updateProductCode" type="text" name="updateProductCode" class="form-control"
                                ng-model="updateProductCode" readonly>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="updateProductPrefix"> Product Prefix <span class="text-warning">*</span> </label>
                                <input id="updateProductPrefix" type="text" name="updateProductPrefix" class="form-control"
                                ng-model="updateProductPrefix" ng-change="generateUpdateProductPrefix(updateProductPrefix)" ng-readonly="isPrefixReadOnly">

                                <div class="text-danger mt-1 text-xs" ng-show="updateForm.updateProductPrefix.$touched && updateForm.updateProductPrefix.$invalid">
                                    <span ng-show="updateForm.updateProductPrefix.$error.required">* The product prefix field is required.</span>
                                </div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <label for="updateDescription"> Description </label>
                                <textarea id="updateDescription" type="text" name="updateDescription" class="form-control"
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
                                    <span ng-show="updateForm.updateStatus.$error.required">* The status field is required.</span>
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
                        You’re about to delete the product. This action won't reflect on the Woo-Commerce website. Are you sure?
                    </div>

                    <div class="text-dark mb-2" ng-show="alertType == 'SyncAlert'">
                        You’re about to sync the product from Woo-Commerce website. This action can't be undone. Are you sure?
                    </div>
                    
                    <div class="col-md-12 mt-4 pt-4 border-top text-right">
                        <button type="button" class="btn bg-gradient-success" ng-click="(alertType == 'DeleteAlert') ? deleteProduct(productId) : syncProduct(productId)"> Yes </button>
                        <button type="button" class="btn bg-gradient-warning" data-bs-dismiss="modal"> No </button>
                    </div>
                </div>
             </div>
        </div>
    </div>


    <!-- Import Product Modal -->
    <div class="modal fade" id="importModal" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white" id="importModalLabel">Import Product</h5>
                    <button type="button" class="btn-close"data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    
                    <form name="importProductForm" method="post" ng-submit="importProduct()">
                        @csrf
                        <div class="row">
                            <div class="col-sm-12 mb-4 mt-2 text-center">
                                <a href="files/templates/product_import_template.xlsx" class="btn bg-gradient-warning mb-0 w-60" download><i class="fa fa-download me-1"></i> Download Template</a>
                            </div>
                            <div class="col-sm-12 mb-2">
                                <label for="importFile"> Import File (.xlsx) <span class="text-warning">*</span> </label>
                                <input type="file" id="importFile" ng-model="importFile" name="importFile" class="form-control"
                                    required>
                            </div>
                            <div class="text-danger mb-2 text-sm" id="importProduct-error-res"></div>
                            <div class="text-sm mt-2">
                                <span class="text-dark font-weight-bolder"> Note: </span> 
                                <span class="text-warning ms-2"> * License code can't be changed once created. </span>
                                <div class="text-warning ms-5"> * Please don't remove or edit the header row. </div>
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
