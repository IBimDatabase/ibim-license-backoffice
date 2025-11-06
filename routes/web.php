<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ProductLicenseKeysController;
use App\Http\Controllers\LicenseTypesController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\PackagesController;
use App\Http\Controllers\WPAuthController;
use App\Http\Controllers\OrdersController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/* Auth Route*/

Route::get('/', [AuthController::class, 'login'])->name('login');
Route::get('login', [AuthController::class, 'login'])->name('login');
//Route::get('forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password');
//Route::get('sign-up', [AuthController::class, 'signUp'])->name('sign-up');

Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
Route::get('product-list', [ProductsController::class, 'productList'])->name('product-list');
Route::get('package-list', [PackagesController::class, 'packageList'])->name('package-list');
Route::get('license-list', [ProductLicenseKeysController::class, 'licenseList'])->name('license-list');
Route::get('license-list-dev', [ProductLicenseKeysController::class, 'licenseListV2'])->name('license-list-dev');
Route::get('expired-license-list', [ProductLicenseKeysController::class, 'expiredLicenseList'])->name('expired-license-list');
Route::get('license-type-list', [LicenseTypesController::class, 'licenseTypeList'])->name('license-type-list');
Route::get('user-profile', [UsersController::class, 'userProfile'])->name('user-profile');
Route::get('user-management', [UsersController::class, 'userManagement'])->name('user-management');
Route::get('role-list', [RolesController::class, 'roleList'])->name('role-list');
Route::get('orders', [OrdersController::class, 'ordersList'])->name('orders');
Route::get('purchase-report-list', [ProductLicenseKeysController::class, 'purchaseReport'])->name('purchase-report-list');
Route::get('expire-report-list', [ProductLicenseKeysController::class, 'expireReport'])->name('expire-report-list');

Route::get('wp/get/token', [WPAuthController::class, 'getToken']);
