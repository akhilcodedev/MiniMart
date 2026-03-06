<?php

use Illuminate\Support\Facades\Route;
use Modules\Base\Http\Controllers\BaseController;
use Modules\Base\Http\Controllers\SiteMapController;

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
    Route::resource('base', BaseController::class)->names('base');
    Route::get('getuserhealthcheck', [BaseController::class, 'getUserHealthCheck'])->name('getUserHealthCheck');
});
