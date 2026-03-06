<?php

use Illuminate\Support\Facades\Route;
use Modules\Api\Http\Controllers\ApiController;
use Modules\Api\Http\Controllers\AuthController;
use Modules\Api\Http\Controllers\CartController;
use Modules\Api\Http\Controllers\FeedController;
use Modules\Api\Http\Controllers\OrderController;
use Modules\Api\Http\Controllers\BannerController;
use Modules\Api\Http\Controllers\ProductController;
use Modules\Api\Http\Controllers\CategoryController;
use Modules\Api\Http\Controllers\CmsBlockController;
use Modules\Api\Http\Controllers\CustomerController;
use Modules\Api\Http\Controllers\HotDealsController;
use Modules\Api\Http\Controllers\WishListController;
use Modules\Api\Http\Controllers\GuestCartController;
use Modules\Api\Http\Controllers\NewsLetterController;
use Modules\Api\Http\Controllers\SiteMapApiController;
use Modules\Api\Http\Controllers\PaymentMethodController;
use Modules\Api\Http\Controllers\ProductsReviewController;
use Modules\Api\Http\Controllers\ShippingMethodController;
use Modules\Api\Http\Controllers\AttributeFilterController;
use Modules\Api\Http\Controllers\CartApiCheckOutController;
use Modules\Api\Http\Controllers\OssConfigurationController;
use Modules\WebConfigurationManagement\Http\Controllers\OtpConfigurationController;



/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
*/
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('api', ApiController::class)->names('api');
    Route::post('order/status-update', [OrderController::class, 'updateOrderStatus']);

    Route::get('order/{orderId}', [OrderController::class, 'getOrderDetails']);
    Route::get('order/{orderId}/download-invoice', [OrderController::class, 'downloadInvoiceApi']);

    Route::put('order/update/{orderId}', [OrderController::class, 'updateOrder']);

    Route::post('create/new/order', [OrderController::class, 'createOrder']);
    Route::post('/order/new/status-update', [OrderController::class, 'updateOrderStatus']);

    Route::post('/payment/success', [OrderController::class, 'paymentSuccess']);
    Route::get('/payment/cancel', [OrderController::class, 'paymentCancel']);
    Route::get('/payment/history', [OrderController::class, 'getPaymentHistory']);

    Route::get('get/profile', [CustomerController::class, 'getProfile']);
    Route::post('update/profile', [CustomerController::class, 'updateProfile']);
    Route::get('get/customer-orders', [OrderController::class, 'fetchUserOrders']);
    Route::get('get/customer-order-conformation', [OrderController::class, 'getLatestOrder']);

    Route::get('logout', [AuthController::class, 'logout']);
    Route::get('get/customer-addresses', [CustomerController::class, 'getAddresses']);
    Route::get('get/customer-address/{id}', [CustomerController::class, 'getAddressById']);
    Route::put('customer/change-password', [AuthController::class, 'changePassword']);
    Route::post('update/address/{id}', [CustomerController::class, 'updateAddress']);
    Route::delete('delete/address/{id}', [CustomerController::class, 'deleteAddress']);
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::post('/cart/update', [CartController::class, 'updateCartItem']);
    Route::get('/getCart', [CartController::class, 'getCart']);
    Route::get('/cart/getTotal', [CartController::class, 'getTotal']);
    Route::get('/get/cart/cross-selling', [CartController::class, 'getCrossSellingItems']);

    Route::post('/cart/delete', [CartController::class, 'removeCartItem']);
    Route::post('/wishlist/add', [WishListController::class, 'addToWishList']);
    Route::get('/wishlist/get', [WishListController::class, 'getWishListItems']);
    Route::put('/wishlist/update/{id}', [WishListController::class, 'updateWishListItem']);
    Route::delete('/wishlist/delete/{id}', [WishListController::class, 'deleteWishListItem']);

    Route::get('/get/cart/address', [CartApiCheckOutController::class, 'getAddressByUser']);
    Route::post('/create/cart/address', [CartApiCheckOutController::class, 'CreateCartAddress']);
    Route::put('/cart/{cart_id}/update-address', [CartApiCheckOutController::class, 'UpdateCartAddress']);

    Route::get('/cart/coupon/list', [CartApiCheckOutController::class, 'couponList']);
    Route::post('/cart/coupon/apply', [CartApiCheckOutController::class, 'applyCoupon']);
    Route::post('/cart/coupon/remove', [CartApiCheckOutController::class, 'removeCoupon']);

    Route::delete('/cart/address/delete/{id}', [CartApiCheckOutController::class, 'deleteAddress']);
    Route::put('/get/cart/customer/address/{id}', [CartApiCheckOutController::class, 'getCartCustomerAddress']);

    Route::get('/cart/grand/total', [CartApiCheckOutController::class, 'grandTotal']);
    Route::put('/carts/shipping-method', [CartApiCheckOutController::class, 'updateShippingMethod']);

    Route::put('/review/update/{review}', [ProductsReviewController::class, 'update']);
    Route::delete('/review/delete/{review}', [ProductsReviewController::class, 'destroy']);
    Route::post('/review/post', [ProductsReviewController::class, 'store']);
    Route::get('/review/product/{productId}', [ProductsReviewController::class, 'showByProduct']);

    Route::post('/oss-configurations', [OssConfigurationController::class, 'store']);
    Route::put('/oss-configurations/{id}', [OssConfigurationController::class, 'update']);
    Route::delete('/oss-configurations/{id}', [OssConfigurationController::class, 'destroy']);
});

Route::get('/hot-deals', [HotDealsController::class, 'HotDealsIndex']);          
Route::post('/hot-deals/store', [HotDealsController::class, 'HotDealsStore']);          
Route::get('/hot-deals/{id}', [HotDealsController::class, 'HotDealsShow']);        
Route::put('/hot-deals/{id}', [HotDealsController::class, 'HotDealsUpdate']);     
Route::delete('/hot-deals/{id}', [HotDealsController::class, 'HotDealsDestroy']);   

Route::post('/oss-configurations', [OssConfigurationController::class, 'store']);

Route::get('/reviews/{productId}', [ProductsReviewController::class, 'show']);
Route::get('/review/get', [ProductsReviewController::class, 'index']);


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/refresh-token', [AuthController::class, 'refreshToken']);

Route::get('get/urKeyType/{urlKey}', [CategoryController::class, 'checkUrlKey']);

Route::get('get/categories', [CategoryController::class, 'getAllCategory']);
Route::get('get/categories/id/{id}', [CategoryController::class, 'getCategoryById']);
Route::get('get/categories/slug/{slug}', [CategoryController::class, 'getCategoryBySlug']);
Route::get('get/categories/images/{filename}', [CategoryController::class, 'getCategoryImage']);

Route::post('/cart/add', [GuestCartController::class, 'addToCart']);
Route::post('/cart/update', [GuestCartController::class, 'updateCartItem']);
Route::get('/cart', [GuestCartController::class, 'getCart']);
Route::get('/cart/getTotal', [GuestCartController::class, 'getTotal']);
Route::get('/cart/cross-selling-items', [GuestCartController::class, 'getCrossSellingItems']);
Route::post('/cart/delete', [GuestCartController::class, 'removeCartItem']);

Route::get('/products', [ProductController::class, 'getAllProducts']);
Route::get('/products/id/{id}', [ProductController::class, 'getProductById']);
Route::get('/products/slug/{slug}', [ProductController::class, 'getProductBySlug']);
Route::get('/products/slug/{slug}/{variant_slug}', [ProductController::class, 'getProductBySlugWithVariantSlug']);
Route::post('products/productsByCategory', [ProductController::class, 'getProductsByCategoieIds']);
Route::get('products/categorySlug/{slug}', [ProductController::class, 'getProductsByCategorySlug']);
Route::get('/get/related/product', [ProductController::class, 'getRelatedProducts']);
Route::get('/product/search', [ProductController::class, 'searchProduct']);

Route::get('/banners', [BannerController::class, 'getAllBanners']);
Route::get('/banners/id/{id}', [BannerController::class, 'getBannersById']);
Route::get('/banner/slug/{slug}', [BannerController::class, 'getBannersBySlug']);
Route::get('/banners/images/{filename}', [BannerController::class, 'showImage']);

Route::get('/shipping-methods', [ShippingMethodController::class, 'index']);
Route::get('/shipping-methods/{id}', [ShippingMethodController::class, 'show']);

Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
Route::get('/payment-methods/{id}', [PaymentMethodController::class, 'show']);
Route::post('/shipping/calculate', [ShippingMethodController::class, 'calculateShipping']);

Route::get('/newsletter', [NewsLetterController::class, 'index']);
Route::post('/newsletters', [NewsLetterController::class, 'store']);
Route::put('/newsletters/{id}', [NewsLetterController::class, 'update']);
Route::delete('/newsletters/{id}', [NewsLetterController::class, 'destroy']);
Route::post('/newsletters/{id}/send-email', [NewsLetterController::class, 'sendEmail']);

Route::get('get/cms-block/id/{id}', [CmsBlockController::class, 'getBlockById']);
Route::get('get/cms-block/identifier/{key}', [CmsBlockController::class, 'getBlockByIdentifier']);

Route::get('get/allFilterAttributes', [AttributeFilterController::class, 'getAllFilterAttributes']);
Route::post('get/getProductsByAttributes', [AttributeFilterController::class, 'getProductsByAttributes']);

Route::get('/generate-sitemap', [SiteMapApiController::class, 'generate']);
Route::get('/siteMap', [SiteMapApiController::class, 'getSiteMap'])->name('api.siteMap');

Route::get('/feed/google', [FeedController::class, 'generateGoogleFeed'])->name('api.feed.google');
Route::get('/feedData', [FeedController::class, 'getProductFeedXml'])->name('api.feed.get');


