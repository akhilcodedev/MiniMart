<?php

use Illuminate\Support\Facades\Route;
use Modules\UserPermission\Http\Controllers\UserPermissionController;
use Modules\UserPermission\Http\Controllers\Roles\RoleController;
use Modules\UserPermission\Http\Controllers\Roles\RolePermissionController;
use Modules\UserPermission\Http\Controllers\Roles\UserRoleController;
use Modules\UserPermission\Http\Controllers\User\UserController;
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
    Route::resource('userpermission', UserPermissionController::class)->names('userpermission');
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    Route::get('/roles/assign-permissions', [RolePermissionController::class, 'index'])->name('roles.assign-permissions.index');
    Route::post('/roles/assign-permissions', [RolePermissionController::class, 'assign'])->name('roles.assign-permissions');
    Route::post('/roles/assign-permissions-user', [RolePermissionController::class, 'assignUser'])->name('roles.assign-permissions-user');
    Route::get('/roles/{role}/edit-permissions', [RolePermissionController::class, 'edit'])->name('roles.edit-permissions-form');
    Route::put('/roles/{role}/edit-permissions', [RolePermissionController::class, 'update'])->name('roles.edit-permissions');
    Route::put('/roles/{role}/edit-permissions-user', [RolePermissionController::class, 'updateUser'])->name('roles.edit-permissions-user');

    Route::get('/users/assign-roles', [UserRoleController::class, 'create'])->name('users.assign-roles');
    Route::post('/users/assign-roles', [UserRoleController::class, 'store'])->name('users.assign-roles.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('user.edit');
    Route::post('/users/{user}/update', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserRoleController::class, 'deleteUser'])->name('users.delete');
    Route::get('/check-role-admin-status/{id}', [RolePermissionController::class, 'checkAdminStatus']);
    Route::post('/users/{user}/edit-roles', [UserRoleController::class, 'update'])->name('users.edit-roles.update');

    // User Routes
    Route::get('/users', [UserController::class, 'index'])->name('user.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('user.create');
    Route::post('/users', [UserController::class, 'store'])->name('user.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('user.edit');
    Route::put('/user/{user}', [UserController::class, 'update'])->name('user.update');
    Route::delete('/user/{user}', [UserController::class, 'destroy'])->name('user.destroy');
    Route::get('/profile', [UserController::class, 'userProfilePage'])->name('user.profile');

    Route::get('/admin/profile', [UserController::class, 'adminProfilePage'])->name('admin.profile');
    Route::put('/update-profile', [UserController::class, 'updateProfile'])->name('user.profile.update');
    Route::put('/admin/update-profile', [UserController::class, 'updateAdminProfile'])->name('admin.profile.update');
    Route::post('/admin/update-password', [UserController::class, 'updateAdminPassword'])->name('admin.update.password');

    Route::post('/admin/update-profile-image', [UserController::class, 'updateAdminProfileImage'])->name('admin.update.profile.image');
});
