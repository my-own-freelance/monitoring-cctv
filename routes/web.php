<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Web\WebBuildingController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/logout', [WebAuthController::class, 'logout'])->name('logout');
// AUTH
Route::group(["middleware" => "guest"], function () {
    Route::get('/', [WebAuthController::class, 'login'])->name('login');
});

Route::group(['middleware' => 'auth:web', 'prefix' => 'admin'], function () {
    Route::get("/", [DashboardController::class, 'index'])->name("dashboard");

    // BUILDING
    Route::group(["prefix" => "building"], function () {
        Route::get("/", [WebBuildingController::class, "index"])->name("building");
    });
});
