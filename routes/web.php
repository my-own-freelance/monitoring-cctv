<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Web\WebBuildingController;
use App\Http\Controllers\Web\WebCctvController;
use App\Http\Controllers\Web\WebFloorController;
use App\Http\Controllers\Web\WebUserController;
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
    Route::get("/building", [WebBuildingController::class, "index"])->name("building");
    Route::get("/floor", [WebFloorController::class, "index"])->name("floor");
    Route::get("/cctv", [WebCctvController::class, "index"])->name("cctv");
    Route::get("/user", [WebUserController::class, "index"])->name("user");
    Route::get('/account', [WebAuthController::class, 'account'])->name('account');
    Route::get('/cctv/export-csv', [WebCctvController::class, 'exportCsv']);
});
