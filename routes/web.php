<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\LoginLogController;
use App\Http\Controllers\Admin\PusakaUserController;

// Auth Routes
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Users
    Route::middleware('permission:view-users')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/data', [UserController::class, 'data'])->name('users.data');
        Route::post('/users', [UserController::class, 'store'])->middleware('permission:create-users')->name('users.store');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::put('/users/{user}', [UserController::class, 'update'])->middleware('permission:edit-users')->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('permission:delete-users')->name('users.destroy');
    });

    // Roles
    Route::middleware('permission:view-roles')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/data', [RoleController::class, 'data'])->name('roles.data');
        Route::post('/roles', [RoleController::class, 'store'])->middleware('permission:create-roles')->name('roles.store');
        Route::get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->middleware('permission:edit-roles')->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:delete-roles')->name('roles.destroy');
    });

    // Permissions
    Route::middleware('permission:view-permissions')->group(function () {
        Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::get('/permissions/data', [PermissionController::class, 'data'])->name('permissions.data');
        Route::post('/permissions', [PermissionController::class, 'store'])->middleware('permission:create-permissions')->name('permissions.store');
        Route::get('/permissions/{permission}', [PermissionController::class, 'show'])->name('permissions.show');
        Route::put('/permissions/{permission}', [PermissionController::class, 'update'])->middleware('permission:edit-permissions')->name('permissions.update');
        Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->middleware('permission:delete-permissions')->name('permissions.destroy');
        Route::post('/permissions/scan', [PermissionController::class, 'scan'])->middleware('permission:scan-permissions')->name('permissions.scan');
    });

    // Pusaka Users
    Route::middleware('permission:view-users')->group(function () {
        Route::get('/pusaka-users', [PusakaUserController::class, 'index'])->name('pusaka-users.index');
        Route::get('/pusaka-users/data', [PusakaUserController::class, 'data'])->name('pusaka-users.data');
        Route::post('/pusaka-users', [PusakaUserController::class, 'store'])->name('pusaka-users.store');
        Route::get('/pusaka-users/{pusakaUser}', [PusakaUserController::class, 'show'])->name('pusaka-users.show');
        Route::put('/pusaka-users/{pusakaUser}', [PusakaUserController::class, 'update'])->name('pusaka-users.update');
        Route::delete('/pusaka-users/{pusakaUser}', [PusakaUserController::class, 'destroy'])->name('pusaka-users.destroy');
    });

    // Login Logs
    Route::middleware('permission:view-users')->group(function () {
        Route::get('/login-logs', [LoginLogController::class, 'index'])->name('login-logs.index');
        Route::get('/login-logs/data', [LoginLogController::class, 'data'])->name('login-logs.data');
    });
});
