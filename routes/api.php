<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductLicenseKeysController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\LicenseTypesController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\PackagesController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\WPProductController;
use App\Http\Controllers\WPProductAttributeController;
use App\Http\Controllers\WPOrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


/* Auth API */
Route::post('authenticate', [AuthController::class, 'authenticate'])->name('authenticate');

Route::middleware('auth:api')->group(function () {
    Route::get('order/view/{id}', [OrdersController::class, 'view_order_data']);
    Route::post('order/create', [OrdersController::class, 'create_order_data']);

    /* Auth API */
    Route::post('change/password', [AuthController::class, 'changePassword'])->name('changePassword');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');


    /* Dashboard API */
    Route::get('get/summary', [DashboardController::class, 'getSummary']);
    Route::get('get/today-purchases', [DashboardController::class, 'getTodayPurchases']);
    Route::get('get/product-based/license-summary', [DashboardController::class, 'getProductBasedLicenseSummary']);


    /* Product API */
    Route::get('get/products', [ProductsController::class, 'getProducts']);
    Route::post('add/product', [ProductsController::class, 'addProduct']);
    Route::post('update/product', [ProductsController::class, 'updateProduct']);
    Route::post('import/product', [ProductsController::class, 'importProduct']);
    Route::get('export/product', [ProductsController::class, 'exportProduct']);
    Route::post('delete/product', [ProductsController::class, 'deleteProduct']);
    Route::post('wp/product/sync', [ProductsController::class, 'syncWooCommerceProduct']);

    /* LicenseType API */
    Route::get('get/licenseTypes/{id}', [LicenseTypesController::class, 'viewLicenseTypes']);
    Route::get('get/licenseTypes', [LicenseTypesController::class, 'getLicenseTypes']);
    Route::post('add/licenseType', [LicenseTypesController::class, 'addLicenseType']);
    Route::post('update/licenseType', [LicenseTypesController::class, 'updateLicenseType']);
    Route::post('import/licenseType', [LicenseTypesController::class, 'importLicenseType']);
    Route::get('export/licenseType', [LicenseTypesController::class, 'exportLicenseType']);
    Route::post('delete/licenseType', [LicenseTypesController::class, 'deleteLicenseType']);

    /* User API */
    Route::get('get/authenticated-user', [UsersController::class, 'getAuthenticatedUser']);


    /***** Wordpress API Starts Here *****/

    /* Product API */
    Route::get('wp/get/products', [WPProductController::class, 'getProducts']);
    Route::post('wp/create/product', [WPProductController::class, 'createProduct']);
    Route::post('wp/update/product', [WPProductController::class, 'updateProduct']);
    Route::post('wp/delete/product', [WPProductController::class, 'deleteProduct']);

    /* Product Attribute API */
    Route::get('wp/get/products/attribute_terms', [WPProductAttributeController::class, 'getProductAttributeTerms']);
    Route::post('wp/create/product/attribute_term', [WPProductAttributeController::class, 'createProductAttributeTerms']);
    Route::post('wp/update/product/attribute_term', [WPProductAttributeController::class, 'updateProductAttributeTerms']);
    Route::post('wp/delete/product/attribute_term', [WPProductAttributeController::class, 'deleteProductAttributeTerms']);

    /***** Wordpress API End Here *****/
});


/* Admin users only allowed */
Route::middleware(['auth:api', 'isAdminUsers'])->group(function () {

    /* Role API */
    Route::get('get/roles', [RolesController::class, 'getRoles']);
    Route::post('add/role', [RolesController::class, 'addRole']);
    Route::post('update/role', [RolesController::class, 'updateRole']);
    
    /* User API */
    Route::get('get/users', [UsersController::class, 'getUsers']);
    Route::post('add/user', [UsersController::class, 'addUser']);
    Route::post('update/user', [UsersController::class, 'updateUser']);

    /* License API */
    Route::post('license/deactivate', [ProductLicenseKeysController::class, 'licenseDeactivate']);
    Route::post('license/activate', [ProductLicenseKeysController::class, 'licenseActivate']);
    Route::post('license/renewal', [ProductLicenseKeysController::class, 'licenseRenewal']);
    Route::post('reset/mac-address', [ProductLicenseKeysController::class, 'resetMacAddress']);
    Route::post('v2/reset/mac-address', [ProductLicenseKeysController::class, 'resetMacAddressV2']);
    Route::post('get/license/history', [ProductLicenseKeysController::class, 'getLicenseHistory']);
    Route::get('get/licenses', [ProductLicenseKeysController::class, 'getLicenses']);
    
    Route::post('order/subscription/renewal', [ProductLicenseKeysController::class, 'renew_existing_orders']);
    Route::post('order/subscription/refund', [ProductLicenseKeysController::class, 'cancel_and_refund_subscription']);
    Route::post('get/license-details', [ProductLicenseKeysController::class, 'getLicenseDetails']);
    Route::post('get/actual-license', [ProductLicenseKeysController::class, 'getActualLicense'])->middleware('auth:api');
    Route::get('get/product-based/license-count', [ProductLicenseKeysController::class, 'getProductBasedLicenseCount']);
    Route::post('delete/license', [ProductLicenseKeysController::class, 'deleteLicenseKey']);
    Route::post('order/license/renewal', [ProductLicenseKeysController::class, 'renew_existing_license_key']);
    Route::post('list/active/product/licenses', [ProductLicenseKeysController::class, 'listLicenses']);
    Route::post('list/active/package/licenses', [ProductLicenseKeysController::class, 'listPackageLicenses']);

    /* Package API */
    Route::get('get/packages', [PackagesController::class, 'getPackages']);
    Route::post('add/package', [PackagesController::class, 'addPackage']);
    Route::post('update/package', [PackagesController::class, 'updatePackage']);

    /* Order API */
    Route::get('get/orders', [OrdersController::class, 'getOrders']);
    Route::get('export/order', [OrdersController::class, 'exportOrder']);
    Route::post('wp/order/sync', [OrdersController::class, 'syncWooCommerceOrder']);
});
/* License API */
Route::post('license/generate', [ProductLicenseKeysController::class, 'generateLicenseKey']);
Route::post('license/availability', [ProductLicenseKeysController::class, 'availabilityOfLicenseKey']);
Route::post('license/validate', [ProductLicenseKeysController::class, 'licenseKeyValidation']);
Route::post('license/purchase', [ProductLicenseKeysController::class, 'licenseKeyDetailsUpdate']);
Route::post('v2/license/activation', [ProductLicenseKeysController::class, 'licenseActivationV2']);
Route::post('license/products', [ProductLicenseKeysController::class, 'getLicenseProducts']);
Route::post('license/customer-update', [ProductLicenseKeysController::class, 'licenseKeyDetailsUpdate']);

Route::post('wp/send/product-info', [WPProductController::class, 'sendProductInfo']);
Route::post('wp/send/order-info', [WPOrderController::class, 'sendOrderInfo']);

Route::post('export/license/userwise', [ProductLicenseKeysController::class, 'exportLicenseWithUser']);



