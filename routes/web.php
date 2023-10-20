<?php

use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get(
    '/',
    [App\Http\Controllers\Admin\LoginController::class, 'showLogin']
)->name('login');

Route::get('/login', [App\Http\Controllers\Admin\LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [App\Http\Controllers\Admin\LoginController::class, 'login'])->name('login.attempt');

Route::group(['middleware' => 'auth'], function () {

    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboardajax', [App\Http\Controllers\Admin\DashboardController::class, 'indexAjax'])->name('dashboardajax');

    Route::get('/logout', [App\Http\Controllers\Admin\DashboardController::class, 'logout'])->name('logout');

    // categories
    Route::get('/categories', [App\Http\Controllers\Admin\CategoriesController::class, 'index'])->name('category');
    
    Route::get('/user/profile', [App\Http\Controllers\Admin\UserController::class, 'userprofile'])->name('user.profile');
    Route::post('/user/update/{id}', [App\Http\Controllers\Admin\UserController::class, 'userupdate'])->name('user.update');
    
});
