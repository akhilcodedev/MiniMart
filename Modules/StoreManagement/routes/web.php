<?php

use Illuminate\Support\Facades\Route;
use Modules\StoreManagement\Http\Controllers\StoreController;
use Modules\StoreManagement\Http\Controllers\StoreManagementController;

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

Route::group(["middleware" => "auth"], function () {
    Route::get('/stores/index', [StoreManagementController::class, 'index'])->name('stores.index');
    Route::get('/stores/create', [StoreManagementController::class, 'create'])->name('stores.create');
    Route::post('/stores', [StoreManagementController::class, 'store'])->name('stores.store');
    Route::get('/stores/{store}', [StoreManagementController::class, 'show'])->name('stores.show');
    Route::get('/stores/{store}/edit', [StoreManagementController::class, 'edit'])->name('stores.edit');
    Route::get('/stores/update/{store}', [StoreManagementController::class, 'update'])->name('stores.update');
    Route::delete('/stores/{store}', [StoreManagementController::class, 'destroy'])->name('stores.destroy');
    Route::post('/stores-delete', [StoreManagementController::class, 'deleteSelected'])->name('stores.deleteSelected');
    Route::get('/store/switch/{store_id}', [StoreController::class, 'switchStore'])->name('store.switch');
});
