<?php

use Illuminate\Support\Facades\Route;
use Modules\PaymentManagement\Http\Controllers\PaymentManagementController;

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

/*Route::group([], function () {
    Route::resource('paymentmanagement', PaymentManagementController::class)->names('paymentmanagement');
});*/

Route::middleware(['auth'])->group(function () {

    Route::group(["prefix" => 'payment-methods'], function () {
        Route::get('/', [PaymentManagementController::class,'index'])->name('admin.paymentMethods.list');
        Route::post('/filter', [PaymentManagementController::class,'searchPaymentMethodByFilters'])->name('admin.paymentMethods.searchByFilters');
        Route::get('/edit/{methodId}', [PaymentManagementController::class,'edit'])->name('admin.paymentMethods.edit');
        Route::post('/update/{methodId}', [PaymentManagementController::class,'update'])->name('admin.paymentMethods.update');
    });

});
