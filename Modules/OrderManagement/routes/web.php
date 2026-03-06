<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\CustomerController;
use Modules\OrderManagement\Http\Controllers\OrderManagementController;

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


Route::group(["middleware" => "auth", "prefix" => 'ordermanagement'],function () {


    
    Route::get('/order-management', [OrderManagementController::class, 'index'])->name('ordermanagement.index');
    Route::get('/order-management/view/{id}', [OrderManagementController::class, 'show'])->name('ordermanagement.show');
    Route::get('/order-management/{orderId}/invoice', [OrderManagementController::class, 'generateInvoice'])->name('ordermanagement.invoice');
    Route::get('/order-management/{orderId}/invoice/download', [OrderManagementController::class, 'downloadInvoice'])->name('ordermanagement.download');
    Route::get('/ordermanagement/customer/details/{id}', [OrderManagementController::class, 'showDetails'])->name('ordermanagement.customer.details');
    Route::get('/orders/{order}/cancel', [OrderManagementController::class, 'cancel'])->name('order.cancel');
    Route::get('/orders/{order}/hold', [OrderManagementController::class, 'hold'])->name('order.hold');
    Route::get('/orders/{order}/ship', [OrderManagementController::class, 'ship'])->name('order.ship');
    Route::get('/orders/{order}/invoice', [OrderManagementController::class, 'invoice'])->name('order.invoice');
    Route::get('/order-management/{orderId}/invoice/download', [OrderManagementController::class, 'downloadInvoice'])->name('ordermanagement.download');
    Route::get('/order/{order_id}/shipping_details', [OrderManagementController::class, 'showShipping'])->name('ordermanagement.shipping_details');
    Route::get('order/{order}/unhold', [OrderManagementController::class, 'unhold'])->name('order.unhold');
});