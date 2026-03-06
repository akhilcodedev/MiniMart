<?php

use Illuminate\Support\Facades\Route;
use Modules\Products\Http\Controllers\HotDealController;
use Modules\Products\Http\Controllers\ProductsController;
use Modules\Products\Http\Controllers\ProductsReviewController;
use Modules\Products\Http\Controllers\ProductVariantController;
use Modules\Products\Http\Controllers\ProductImportExportController;
use Modules\Products\Http\Controllers\ProductVariantOptionController;
use Modules\Products\Http\Controllers\ProductReviewAttributeController;


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

//Route::group([], function () {
//    Route::resource('products', ProductsController::class)->names('products');
//});
Route::group(["middleware" => ["auth", "dynamic.roles"], "prefix" => 'products'], function () {

    Route::get('/', [ProductsController::class, 'index'])->name('products.index');
    Route::get('/create', [ProductsController::class, 'create'])->name('products.create');
    Route::post('/store', [ProductsController::class, 'store'])->name('products.store');
    Route::get('/edit/{id}', [ProductsController::class, 'edit'])->name('products.edit');
    Route::post('/update/{id}', [ProductsController::class, 'update'])->name('products.update');
    Route::post('/getAllProducts', [ProductsController::class, 'getAllProducts'])->name('products.all');
    Route::post('/delete', [ProductsController::class, 'delete'])->name('products.delete');
    Route::post('/bulk-delete', [ProductsController::class, 'bulkDelete'])->name('products.bulk-delete');
    Route::post('/get-product-attributes-content', [ProductsController::class, 'getProductAttributeContent'])->name('products.get-product-attributes-content');

    Route::get('/product/{slug}', [ProductsController::class, 'show'])
        ->name('product.show');

    // Fetch related products with pagination (AJAX)
    Route::get('/products/related', [ProductsController::class, 'getRelatedProducts'])->name('products.related');
    Route::post('/products/save-related', [ProductsController::class, 'saveRelatedProducts'])->name('products.saveRelated');

    Route::get('/products/crossSelling', [ProductsController::class, 'getCrossSellingProducts'])->name('products.crossSelling');
    Route::post('/products/save-crossSelling', [ProductsController::class, 'saveCrossSellingProducts'])->name('products.saveCrossSelling');
    //Review
    Route::get('/reviews', [ProductsReviewController::class, 'index'])->name('products_review.index');
    Route::get('/reviews/create', [ProductsReviewController::class, 'create'])->name('products_review.create');
    Route::post('/reviews', [ProductsReviewController::class, 'store'])->name('products_review.store');
    Route::get('/reviews/{review}/edit', [ProductsReviewController::class, 'edit'])->name('products_review.edit');
    Route::put('/reviews/{review}', [ProductsReviewController::class, 'update'])->name('products_review.update');
    Route::delete('/reviews/{review}', [ProductsReviewController::class, 'destroy'])->name('products_review.destroy');

    Route::post('/products/import', [ProductImportExportController::class, 'import'])->name('products.import');
    Route::get('/products/export', [ProductImportExportController::class, 'export'])->name('products.export');


    //Review Attribute
    Route::get('/review-attributes', [ProductReviewAttributeController::class, 'index'])->name('product_review_attributes.index');
    Route::get('/review-attributes/create', [ProductReviewAttributeController::class, 'create'])->name('product_review_attributes.create');
    Route::post('/review-attributes', [ProductReviewAttributeController::class, 'store'])->name('product_review_attributes.store');
    Route::get('/review-attributes/{productReviewAttribute}', [ProductReviewAttributeController::class, 'show'])->name('product_review_attributes.show');
    Route::get('/review-attributes/{productReviewAttribute}/edit', [ProductReviewAttributeController::class, 'edit'])->name('product_review_attributes.edit');
    Route::put('/review-attributes/{productReviewAttribute}', [ProductReviewAttributeController::class, 'update'])->name('product_review_attributes.update');
    Route::delete('/review-attributes/{productReviewAttribute}', [ProductReviewAttributeController::class, 'destroy'])->name('product_review_attributes.destroy');

    Route::get('product-variant-list/{id}', [ProductVariantController::class, 'viewAllVariants'])->name('product.variants.all');
    Route::get('create/variant-product/{id}', [ProductVariantController::class, 'createVariantProduct'])->name('create.variant.product');
    Route::post('store/variant-product', [ProductVariantController::class, 'storeVariantProduct'])->name('store.variant.product');
    Route::post('bulk-delete/variant-products', [ProductVariantController::class, 'deleteBulkVariantProducts'])->name('product.variable.bulk-delete');
    Route::post('delete/variant-product', [ProductVariantController::class, 'deleteVariantProduct'])->name('product.variable.delete');
    Route::get('edit/variant-product/{id}', [ProductVariantController::class, 'editVariantProduct'])->name('product.variant.edit');
    Route::post('update/variant-product/{id}', [ProductVariantController::class, 'updateVariantProduct'])->name('product.variant.update');

    Route::get('allAttributes/{parent_id}', [ProductsController::class,'listAllAttributes'])->name('products.listAllAttributes');
    Route::get('getAttributeOptions', [ProductsController::class,'getAttributeOptions'])->name('products.getAttributeOptions');
    Route::post('storeVariants', [ProductVariantController::class,'storeVariants'])->name('products.storeVariants');


    Route::resource('product-variant-options', ProductVariantOptionController::class)->names('product.variant.options');


    Route::get('/hot-deals', [HotDealController::class, 'index'])->name('hot_deals.index');
    Route::get('/hot-deals/create', [HotDealController::class, 'create'])->name('hot_deals.create');
    Route::post('/', [HotDealController::class, 'store'])->name('store');

    Route::get('/hot-deals/{id}/edit', [HotDealController::class, 'edit'])->name('edit');
    Route::put('/hot-deals/{id}', [HotDealController::class, 'update'])->name('update');
    Route::delete('/hot-deals/{id}', [HotDealController::class, 'destroy'])->name('delete');
    Route::get('/hot-deals/{id}', [HotDealController::class, 'show'])->name('hot_deals.show');
});
