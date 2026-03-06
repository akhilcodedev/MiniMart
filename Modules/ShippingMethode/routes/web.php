<?php

use Illuminate\Support\Facades\Route;
use Modules\ShippingMethode\Http\Controllers\ShippingMethodController;



Route::group(["middleware" => "auth", "prefix" => 'shipping'],function () {
    //Route::get('/', action: [ShippingMethodController::class, 'index'])->name('shipping.index');

    Route::get('/index', [ShippingMethodController::class, 'index'])->name('shipping.index');
    Route::get('/create', [ShippingMethodController::class, 'create'])->name('shipping.create');
    Route::post('/', [ShippingMethodController::class, 'store'])->name('shipping.store');
    Route::get('/{shippingMethod}/edit', [ShippingMethodController::class, 'edit'])->name('shipping.edit');
    Route::put('/{shippingMethod}', [ShippingMethodController::class, 'update'])->name('shipping.update');
    Route::delete('/{shippingMethod}', [ShippingMethodController::class, 'destroy'])->name('shipping.destroy');

});
