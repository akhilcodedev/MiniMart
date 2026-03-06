<?php

use Illuminate\Support\Facades\Route;
use Modules\TaxManagement\Http\Controllers\TaxManagementController;
use Modules\TaxManagement\Http\Controllers\TaxRateController;

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

Route::group(["middleware" => "auth", "prefix" => 'tax'],function () {
//    Route::resource('taxmanagement', TaxManagementController::class)->names('taxmanagement');
    Route::get('/index', [TaxManagementController::class, 'index'])->name('tax.index');
        Route::get('/create', [TaxManagementController::class, 'create'])->name('tax.create');
        Route::post('/store', [TaxManagementController::class, 'store'])->name('tax.store');
        Route::get('/{tax}/edit', [TaxManagementController::class, 'edit'])->name('tax.edit');
        Route::put('update/{tax}', [TaxManagementController::class, 'update'])->name('tax.update');
        Route::delete('destroy/{tax}', [TaxManagementController::class, 'destroy'])->name('tax.destroy');

});


Route::prefix('tax-rates')->group(function () {
    Route::get('/', [TaxRateController::class, 'index'])->name('tax-rates.index');
    Route::get('/create', [TaxRateController::class, 'create'])->name('tax-rates.create');
    Route::post('/', [TaxRateController::class, 'store'])->name('tax-rates.store');
    Route::get('/{taxRate}/edit', [TaxRateController::class, 'edit'])->name('tax-rates.edit');
    Route::put('/{taxRate}', [TaxRateController::class, 'update'])->name('tax-rates.update');
    Route::delete('/{taxRate}', [TaxRateController::class, 'destroy'])->name('tax-rates.destroy');
});
