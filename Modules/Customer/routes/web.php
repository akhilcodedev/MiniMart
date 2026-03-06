<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\CustomerController;
use Modules\Customer\Http\Controllers\CustomerGroups\CustomerGroupsController;

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
Route::group(["middleware" => ["auth", "dynamic.roles"]], function () {
        Route::get('/customer', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    Route::post('/customers-delete', [CustomerController::class, 'bulkDelete'])->name('customers.bulk.delete');
    Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
    Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/filter', [CustomerController::class, 'filter'])->name('customers.filter');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');


    Route::get('/customers/groups/index', [CustomerGroupsController::class, 'index'])->name('customer.groups.index');
    Route::get('customers/group/create', [CustomerGroupsController::class, 'create'])->name('customer.groups.create');
    Route::post('/customers/store', [CustomerGroupsController::class, 'store'])->name('customer.groups.store');
    Route::get('/customer-group/{customer}/edit', [CustomerGroupsController::class, 'edit'])->name('customer.groups.edit');
    Route::put('/customers/{customer}/update', [CustomerGroupsController::class, 'update'])->name('customer.groups.update');
    Route::delete('/customers/{customer}/delete', [CustomerGroupsController::class, 'destroy'])->name('customer.groups.destroy');
    Route::get('customer-groups/filter', [CustomerGroupsController::class, 'filter'])->name('customer.groups.filter');
});

