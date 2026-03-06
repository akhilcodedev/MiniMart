<?php

use Illuminate\Support\Facades\Route;
use Modules\Cart\Http\Controllers\CartController;


Route::group(["middleware" => "auth"], function () {
    //Route::resource('cart', CartController::class)->names('cart');
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::get('/create', [CartController::class,'create'])->name('cart.create');
    Route::post('/getAllCartProducts', [CartController::class,'getAllCartProducts'])->name('cart.all');

});
