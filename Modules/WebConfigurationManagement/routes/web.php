<?php

use Illuminate\Support\Facades\Route;
use Modules\WebConfigurationManagement\Http\Controllers\ImportController;
use Modules\WebConfigurationManagement\Http\Controllers\WebConfigurationManagementController;
use Modules\WebConfigurationManagement\Http\Controllers\Configuration\ConfigurationController;
use Modules\WebConfigurationManagement\Http\Controllers\OssConfigurationController;
use Modules\WebConfigurationManagement\Http\Controllers\OtpConfigurationController;

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

//    Route::get('configure', [ConfigurationController::class, 'index'])->name('configure.index');
    Route::get('/configure', [ConfigurationController::class, 'index'])->name('configure.index');

    Route::get('/web-configs/configure', [ConfigurationController::class, 'showConfigForm'])->name('core.config.show');
    Route::post('configUpdate/{code}', [ConfigurationController::class, 'update'])->name('configure.update');
    Route::post('update/config', [ConfigurationController::class, 'configUpdate'])->name('core.config.update');
    Route::post('update/config/payment-method', [ConfigurationController::class, 'paymentMethodUpdate'])->name('core.config.payment.method.update');
    Route::post('additionalUpdate', [ConfigurationController::class, 'additionalUpdate'])->name('additional.update');

    Route::post('/import', [ImportController::class, 'import'])->name('import');
    Route::get('/download-file/{filename}', [ImportController::class, 'downloadSampleFile'])->name('download.sample.file');


//    Route::get('/config/{form}', function ($form) {
//        return view('webconfigurationmanagement::system-configuration.sytem', ['form' => $form]);
//    })->name('system.config.form');

    Route::get('/system/config/{menu}/{submenu}/{form}', [WebConfigurationManagementController::class, 'showForm'])
        ->name('system.config.form');

//    Route::post('/web-config', [WebConfigurationManagementController::class, 'handleMenuAction'])->name('menu.action');

Route::get('/system/config/tax-configuration/create', [WebConfigurationManagementController::class, 'create'])->name('tax-config.create');
Route::post('/system/config/tax-configuration', [WebConfigurationManagementController::class, 'store'])->name('tax-config.store');

Route::resource('oss-configurations', OssConfigurationController::class);
// Route::get('/otp/configuration', [OTPConfigurationController::class, 'index'])->name('otp.config.form');
// Route::post('/otp/configuration', [OTPConfigurationController::class, 'update'])->name('otp.config.update');
Route::resource('otp', OtpConfigurationController::class);
Route::post('/otp/save-static', [OtpConfigurationController::class, 'saveStatic'])->name('otp.saveStatic');

    Route::post('/system-config/abandoned-cart', [WebConfigurationManagementController::class, 'saveAbandonedCartConfig'])
        ->name('system.config.abandoned-cart.submit');

    Route::post('/system/config/send-test-mail', [WebConfigurationManagementController::class, 'sendTestMail'])
        ->name('system.config.send-test-mail');
});
