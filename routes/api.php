<?php

use App\Http\Controllers\Api\ApiBuildingController;
use App\Http\Controllers\Api\ApiCctvController;
use App\Http\Controllers\Api\ApiFloorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomTemplateController;
use App\Http\Controllers\Web\WebBuildingController;
use App\Http\Controllers\Web\WebCctvController;
use App\Http\Controllers\Web\WebFloorController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// MOBILE API
Route::group(["middleware" => "api"], function () {
    Route::prefix("auth")->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('me', [AuthController::class, 'me']);
    });
});

Route::group(["middleware" => ["api", "auth:api"]], function () {
    // Building
    Route::group(["prefix" => "building"], function () {
        // akses khusus superadmin
        Route::middleware("check.role:superadmin")->group(function () {
            Route::post("/create", [ApiBuildingController::class, "create"]);
            Route::post("/update", [ApiBuildingController::class, "update"]);
            Route::delete("/delete", [ApiBuildingController::class, "destroy"]);
        });

        // akses operator & operator gedung
        Route::get("datatable", [ApiBuildingController::class, "dataTable"]);
        Route::get("{id}/detail", [ApiBuildingController::class, "getDetail"]);
    });

    // Floor
    Route::group(["prefix" => "floor"], function () {
        // akses khusus superadmin
        Route::middleware("check.role:superadmin")->group(function () {
            Route::post("/create", [ApiFloorController::class, "create"]);
            Route::post("/update", [ApiFloorController::class, "update"]);
            Route::delete("/delete", [ApiFloorController::class, "destroy"]);
        });

        // akses operator & operator gedung
        Route::get("datatable", [ApiFloorController::class, "dataTable"]);
        Route::get("/list", [ApiFloorController::class, "list"]);
        Route::get("{id}/detail", [ApiFloorController::class, "getDetail"]);
    });

    // Cctv
    Route::group(["prefix" => "cctv"], function () {
        // akses khusus superadmin
        Route::middleware("check.role:superadmin")->group(function () {
            Route::post("/create", [ApiCctvController::class, "create"]);
            Route::post("/update", [ApiCctvController::class, "update"]);
            Route::delete("/delete", [ApiCctvController::class, "destroy"]);
        });

        // akses operator & operator gedung
        Route::get("datatable", [ApiCctvController::class, "dataTable"]);
        Route::get("{id}/detail", [ApiCctvController::class, "getDetail"]);
    });
});


// WEB API
Route::post("/auth/login/validate", [WebAuthController::class, "validateLogin"]);


Route::prefix("admin")->namespace("admin")->middleware(["check.auth"])->group(function () {
    Route::post("/custom_template/create_update", [CustomTemplateController::class, "saveUpdateData"]);

    // Building
    Route::prefix("building")->group(function () {
        // akses khusus superadmin
        Route::middleware("check.role:superadmin")->group(function () {
            Route::post("/create", [WebBuildingController::class, "create"]);
            Route::post("/update", [WebBuildingController::class, "update"]);
            Route::delete("/delete", [WebBuildingController::class, "destroy"]);
        });

        // akses operator & operator gedung
        Route::get("/datatable", [WebBuildingController::class, "dataTable"]);
        Route::get("/{id}/detail", [WebBuildingController::class, "getDetail"]);
    });

    // Floor
    Route::prefix("floor")->group(function () {
        // akses khusus superadmin
        Route::middleware("check.role:superadmin")->group(function () {
            Route::post("/create", [WebFloorController::class, "create"]);
            Route::post("/update", [WebFloorController::class, "update"]);
            Route::delete("/delete", [WebFloorController::class, "destroy"]);
        });

        // akses operator & operator gedung
        Route::get("/datatable", [WebFloorController::class, "dataTable"]);
        Route::get("/list", [WebFloorController::class, "list"]);
        Route::get("/{id}/detail", [WebFloorController::class, "getDetail"]);
    });

    // Cctv
    Route::prefix("cctv")->group(function () {
        // akses khusus superadmin
        Route::middleware("check.role:superadmin")->group(function () {
            Route::post("/create", [WebCctvController::class, "create"]);
            Route::post("/update", [WebCctvController::class, "update"]);
            Route::delete("/delete", [WebCctvController::class, "destroy"]);
        });

        // akses operator & operator gedung
        Route::get("/datatable", [WebCctvController::class, "dataTable"]);
        Route::get("/{id}/detail", [WebCctvController::class, "getDetail"]);
    });
});
