<?php

use Illuminate\Support\Facades\Route;
use Modules\PriceRuleManagement\Http\Controllers\PriceRuleManagementController;
use Modules\PriceRuleManagement\Http\Controllers\CartPriceRuleManagementController;
use Modules\PriceRuleManagement\Http\Controllers\CatalogPriceRuleController;

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

Route::group(["middleware" => "auth", "prefix" => 'price-rules'], function () {

    Route::group(["prefix" => 'cart'], function () {

        Route::group(["prefix" => 'coupons'], function () {
            Route::get('/', [CartPriceRuleManagementController::class,'index'])->name('priceRule.cart.coupons.index');
            Route::post('/filter', [CartPriceRuleManagementController::class,'searchCouponByFilters'])->name('priceRule.cart.coupons.searchByFilters');
            Route::get('/get-detail', [CartPriceRuleManagementController::class,'getCouponDetail'])->name('priceRule.cart.coupons.getDetail');
            Route::post('/import', [CartPriceRuleManagementController::class,'importCoupon'])->name('priceRule.cart.coupons.import');
            Route::get('/export', [CartPriceRuleManagementController::class,'exportCoupon'])->name('priceRule.cart.coupons.export');
            Route::get('/search-items', [CartPriceRuleManagementController::class,'searchItemsToCoupon'])->name('priceRule.cart.coupons.searchItems');
            Route::get('/search-customers', [CartPriceRuleManagementController::class,'searchCustomersToCoupon'])->name('priceRule.cart.coupons.searchCustomers');
            Route::get('/new', [CartPriceRuleManagementController::class,'newCoupon'])->name('priceRule.cart.coupons.new');
            Route::post('/save', [CartPriceRuleManagementController::class,'saveCoupon'])->name('priceRule.cart.coupons.save');
            Route::get('/edit/{couponId}', [CartPriceRuleManagementController::class,'editCoupon'])->name('priceRule.cart.coupons.edit');
            Route::post('/update/{couponId}', [CartPriceRuleManagementController::class,'updateCoupon'])->name('priceRule.cart.coupons.update');
            Route::post('/delete', [CartPriceRuleManagementController::class,'destroyCoupon'])->name('priceRule.cart.coupons.destroy');
            Route::post('/bulk-delete', [CartPriceRuleManagementController::class,'bulkDeleteCoupon'])->name('priceRule.cart.coupons.bulkDelete');
        });

        Route::group(["prefix" => 'coupon-modes'], function () {
            Route::get('/', [CartPriceRuleManagementController::class,'couponModeList'])->name('priceRule.cart.couponModes.index');
            Route::post('/filter', [CartPriceRuleManagementController::class,'searchCouponModeByFilters'])->name('priceRule.cart.couponModes.searchByFilters');
            Route::post('/import', [CartPriceRuleManagementController::class,'importCouponMode'])->name('priceRule.cart.couponModes.import');
            Route::get('/export', [CartPriceRuleManagementController::class,'exportCouponMode'])->name('priceRule.cart.couponModes.export');
            Route::get('/create', [CartPriceRuleManagementController::class,'couponCreate'])->name('priceRule.cart.couponModes.create');
            Route::post('/store', [CartPriceRuleManagementController::class, 'couponStore'])->name('priceRule.cart.couponModes.store');
            Route::get('/edit/{id}', [CartPriceRuleManagementController::class, 'couponEdit'])->name('priceRule.cart.couponModes.edit');
            Route::post('/update/{id}', [CartPriceRuleManagementController::class, 'couponUpdate'])->name('priceRule.cart.couponModes.update');
            Route::delete('/delete/{id}', [CartPriceRuleManagementController::class, 'couponDestroy'])->name('priceRule.cart.couponModes.destroy');
        });

        Route::group(["prefix" => 'coupon-types'], function () {
            Route::get('/', [CartPriceRuleManagementController::class,'couponTypeList'])->name('priceRule.cart.couponTypes.index');
            Route::post('/filter', [CartPriceRuleManagementController::class,'searchCouponTypeByFilters'])->name('priceRule.cart.couponTypes.searchByFilters');
            Route::post('/import', [CartPriceRuleManagementController::class,'importCouponType'])->name('priceRule.cart.couponTypes.import');
            Route::get('/export', [CartPriceRuleManagementController::class,'exportCouponType'])->name('priceRule.cart.couponTypes.export');
            Route::get('/create', [CartPriceRuleManagementController::class,'couponTypeCreate'])->name('priceRule.cart.couponTypes.create');
            Route::post('/store', [CartPriceRuleManagementController::class, 'couponTypeStore'])->name('priceRule.cart.couponTypes.store');
            Route::get('/edit/{id}', [CartPriceRuleManagementController::class, 'couponTypeEdit'])->name('priceRule.cart.couponTypes.edit');
            Route::post('/update/{id}', [CartPriceRuleManagementController::class, 'couponTypeUpdate'])->name('priceRule.cart.couponTypes.update');
            Route::delete('/delete/{id}', [CartPriceRuleManagementController::class, 'couponTypeDestroy'])->name('priceRule.cart.couponTypes.destroy');
        });

        Route::group(["prefix" => 'coupon-entities'], function () {
            Route::get('/', [CartPriceRuleManagementController::class,'couponEntityList'])->name('priceRule.cart.couponEntities.index');
            Route::post('/filter', [CartPriceRuleManagementController::class,'searchCouponEntityByFilters'])->name('priceRule.cart.couponEntities.searchByFilters');
            Route::post('/import', [CartPriceRuleManagementController::class,'importCouponEntity'])->name('priceRule.cart.couponEntities.import');
            Route::get('/export', [CartPriceRuleManagementController::class,'exportCouponEntity'])->name('priceRule.cart.couponEntities.export');
        });

        Route::get('/download-sample-csv', [CartPriceRuleManagementController::class,'downloadSampleCsv'])->name('priceRule.cart.downloadSampleCsv');

    });
    Route::post('/run-price-rule-indexer', [CatalogPriceRuleController::class,'runIndexer'])->name('run.catalog.price.indexer');
});

Route::group(["middleware" => "auth"], function () {
    Route::resource('catalog-price-rules', CatalogPriceRuleController::class)->names('catalog-price-rules');
    Route::post('catalog-price-rules', [CatalogPriceRuleController::class, 'destroy'])->name('catalog-price-rules.delete');
    Route::post('catalog-price-rule/store', [CatalogPriceRuleController::class, 'store'])->name('catalog-price-rules.store');
    Route::post('catalog-price-rule/update', [CatalogPriceRuleController::class, 'update'])->name('catalog-price-rules.update');
    Route::get('get-rule-values', [CatalogPriceRuleController::class, 'getRuleValues'])->name('get.rule.values');
});
