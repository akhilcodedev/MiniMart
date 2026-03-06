<?php

use Illuminate\Support\Facades\Route;
use Modules\PaymentMethod\Http\Controllers\PaymentMethodController;

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


Route::group(["middleware" => "auth", "prefix" => 'payment'], function () {
    //Route::get('/', action: [ShippingMethodController::class, 'index'])->name('shipping.index');

    Route::get('/payment', [PaymentMethodController::class, 'index'])->name('payment.index');
    // Store a new payment method
    Route::post('/store', [PaymentMethodController::class, 'store'])->name('payment.store');

    Route::get('payment-methods/create', [PaymentMethodController::class, 'create'])->name('payment.create');

    // Get payment method data for editing
    Route::get('payment-methods/edit/{id}', [PaymentMethodController::class, 'edit'])->name('payment.edit');

    // Update a payment method
    Route::put('/payment-methods/{id}', [PaymentMethodController::class, 'update'])->name('payment.update');

    // Delete a payment method
    Route::delete('/delete/{id}', [PaymentMethodController::class, 'destroy'])->name('payment.destroy');
});
