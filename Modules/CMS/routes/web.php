<?php

use Illuminate\Support\Facades\Route;
use Modules\CMS\Http\Controllers\CmsBlockController;
use Modules\CMS\Http\Controllers\CMSController;
use Modules\CMS\Http\Controllers\BannerController;
use Modules\CMS\Http\Controllers\EmailTemplateController;


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


Route::group(["middleware" => ["auth", "dynamic.roles"], "prefix" => 'cms'],function () {
    Route::get('/pages',  [CMSController::class, 'getCmsPages'])->name('cms.pages');
    Route::get('/pages/create', [CMSController::class, 'create'])->name('cms.pages.create');
    Route::post('/pages', [CMSController::class, 'store'])->name('cms.pages.store');
    Route::get('/pages/{id}/edit', [CMSController::class, 'edit'])->name('cms.pages.edit');
    Route::put('/pages/{id}', [CMSController::class, 'update'])->name('cms.pages.update');
    Route::delete('/pages/{id}', [CMSController::class, 'destroy'])->name('cms.pages.destroy');
    Route::post('/cms/pages/bulk-delete', [CMSController::class, 'bulkDelete'])->name('cms.pages.bulk-delete');
    Route::resource('email-template', EmailTemplateController::class)->names('email.templates');
    Route::post('email/templates/bulk-delete', [EmailTemplateController::class, 'bulkDelete'])->name('email.templates.bulk-delete');
    Route::resource('banners', BannerController::class)->except('update')->name('cms','banners.index');
    Route::put('banners/{hero_banner}', [BannerController::class, 'update'])->name('banners.update');
    Route::get('/banners/create', [BannerController::class, 'create'])->name('banners.create');
    Route::delete('cms/banners/{hero_banner}', [BannerController::class, 'destroy'])->name('banners.destroy');
    Route::post('/banners/delete-banner-image', [BannerController::class, 'deleteBannerImage'])->name('banners.delete-banner-image');
    Route::resource('cms-blocks', CmsBlockController::class)->names('cms-blocks');
    Route::post('cms-block/delete', [CmsBlockController::class, 'destroy'])->name('cms-blocks.delete');
});
