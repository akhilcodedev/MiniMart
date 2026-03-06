<?php

use Illuminate\Support\Facades\Route;
use Modules\ProductAttributes\Http\Controllers\ProductAttributesController;
use Modules\ProductAttributes\Http\Controllers\ProductAttributeSetsController;

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

Route::group(["middleware" => ["auth", "dynamic.roles"], "prefix" => 'attributes'], function () {
    Route::get('/', [ProductAttributesController::class,'index'])->name('product.attributes.index');
    Route::post('/getAllAttributes', [ProductAttributesController::class,'getAllAttributes'])->name('product.attributes.all');
    Route::get('/create', [ProductAttributesController::class,'create'])->name('product.attributes.create');
    Route::post('/store', [ProductAttributesController::class,'store'])->name('product.attributes.store');
    Route::get('/edit/{id}', [ProductAttributesController::class,'edit'])->name('product.attributes.edit');
    Route::post('/update/{id}', [ProductAttributesController::class,'update'])->name('product.attributes.update');
    Route::post('/delete', [ProductAttributesController::class,'destroy'])->name('product.attributes.delete');
    Route::post('/bulk-delete', [ProductAttributesController::class,'bulkDelete'])->name('product.attributes.bulk-delete');
    Route::group(["prefix" => 'attribute-sets'], function () {
        Route::get('/', [ProductAttributeSetsController::class,'index'])->name('product.attribute.sets.index');
        Route::post('/getAllAttributeSets', [ProductAttributeSetsController::class,'getAllAttributeSets'])->name('product.attribute.sets.all');
        Route::get('/fetchLinkAttributeToSet', [ProductAttributeSetsController::class,'fetchLinkAttributeToSet'])->name('product.attribute.sets.link.attribute.view');
        Route::post('/linkAttributeToSet', [ProductAttributeSetsController::class,'linkAttributeToSet'])->name('product.attribute.sets.link.attribute.process');
        Route::get('/create', [ProductAttributeSetsController::class,'create'])->name('product.attribute.sets.create');
        Route::post('/store', [ProductAttributeSetsController::class,'store'])->name('product.attribute.sets.store');
        Route::get('/edit/{id}', [ProductAttributeSetsController::class,'edit'])->name('product.attribute.sets.edit');
        Route::post('/update/{id}', [ProductAttributeSetsController::class,'update'])->name('product.attribute.sets.update');
        Route::post('/delete', [ProductAttributeSetsController::class,'destroy'])->name('product.attribute.sets.delete');
        Route::post('/bulk-delete', [ProductAttributeSetsController::class,'bulkDelete'])->name('product.attribute.sets.bulk-delete');
    });
});
