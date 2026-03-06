<?php

use Illuminate\Support\Facades\Route;
use Modules\URLRewrite\Http\Controllers\URLRewriteController;


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

Route::group(["middleware" => "auth", 'prefix' => 'urlrewrite'], function () {
    Route::get('/', [UrlRewriteController::class, 'index'])->name('urlrewrite.index');
    Route::get('/urlrewrite/create', [UrlRewriteController::class, 'create'])->name('urlrewrite.create');
    Route::post('/urlrewrite', [UrlRewriteController::class, 'store'])->name('urlrewrite.store');
    Route::get('/urlrewrite/{id}', [UrlRewriteController::class, 'show'])->name('urlrewrite.show');
    Route::get('/urlrewrite/{id}/edit', [UrlRewriteController::class, 'edit'])->name('urlrewrite.edit');
    Route::put('/urlrewrite/{id}', [UrlRewriteController::class, 'update'])->name('urlrewrite.update'); // Use PUT for updates
    Route::delete('/urlrewrite/{id}', [UrlRewriteController::class, 'destroy'])->name('urlrewrite.destroy');
});

