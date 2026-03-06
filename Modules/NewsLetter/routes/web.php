<?php

use Illuminate\Support\Facades\Route;
use Modules\NewsLetter\Http\Controllers\NewsLetterController;

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
Route::group(["middleware" => "auth", "prefix" => 'newsletters'], function () {

    Route::get('/', [NewsLetterController::class, 'index'])->name('newsletters.index');
    Route::get('/create', [NewsLetterController::class, 'create'])->name('newsletters.create');
    Route::post('/', [NewsLetterController::class, 'store'])->name('newsletters.store');
    Route::get('/{id}/edit', [NewsLetterController::class, 'edit'])->name('newsletters.edit');
    Route::put('/{id}', [NewsLetterController::class, 'update'])->name('newsletters.update');
    Route::delete('/{id}', [NewsLetterController::class, 'destroy'])->name('newsletters.destroy');
    Route::post('/{id}/send-email', [NewsLetterController::class, 'sendEmail'])->name('newsletters.sendEmail');
});
