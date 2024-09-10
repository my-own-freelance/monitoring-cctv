<?php

use App\Http\Controllers\Api\ApiBuildingController;
use App\Http\Controllers\Api\ApiCctvController;
use App\Http\Controllers\Api\ApiFloorController;
use App\Http\Controllers\Api\ApiUserCctvController;
use App\Http\Controllers\Api\ApiUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomTemplateController;
use App\Http\Controllers\Web\WebBuildingController;
use App\Http\Controllers\Web\WebCctvController;
use App\Http\Controllers\Web\WebFloorController;
use App\Http\Controllers\Web\WebUserCctvController;
use App\Http\Controllers\Web\WebUserController;
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

Route::get("/custom_template/detail", [CustomTemplateController::class, "detail"]);
Route::group(["middleware" => ["api", "auth:api"]], function () {
    Route::post("/custom_template/create_update", [CustomTemplateController::class, "saveUpdateData"]);

    // Building
    Route::group(["prefix" => "building"], function () {
        // akses khusus superadmin
        Route::middleware("check.role:superadmin")->group(function () {
            Route::post("/create", [ApiBuildingController::class, "create"]);
            Route::post("/update", [ApiBuildingController::class, "update"]);
            Route::delete("/delete", [ApiBuildingController::class, "destroy"]);
        });

        // akses operator & operator cctv
        Route::get("datatable", [ApiBuildingController::class, "dataTable"]);
        Route::get("/list", [ApiBuildingController::class, "list"]);
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

        // akses operator & operator cctv
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

        // akses operator & operator cctv
        Route::get("datatable", [ApiCctvController::class, "dataTable"]);
        Route::get("/list", [ApiCctvController::class, "list"]);
        Route::get("{id}/detail", [ApiCctvController::class, "getDetail"]);
    });

    // User
    Route::group(["prefix" => "user"], function () {
        // akses khusus superadmin
        Route::middleware("check.role:superadmin")->group(function () {
            Route::post("/create", [ApiUserController::class, "create"]);
            Route::post("/update", [ApiUserController::class, "update"]);
            Route::delete("/delete", [ApiUserController::class, "destroy"]);
            Route::post("/update-status", [ApiUserController::class, "updateStatus"]);
        });

        // akses operator & operator cctv
        Route::get("/datatable", [ApiUserController::class, "dataTable"]);
        Route::get("/{id}/detail", [ApiUserController::class, "getDetail"]);
    });

    // ACCOUNT
    Route::group(["prefix" => "account"], function () {
        Route::get("/detail", [AuthController::class, "detail"]);
        Route::post("/update", [AuthController::class, "update"]);
    });

    // User CCTV - only oprator cctv
    Route::group(["prefix" => "user-cctv"], function () {
        Route::middleware("check.role:superadmin")->group(function () {
            Route::post("/create", [ApiUserCctvController::class, "create"]);
            Route::delete("/delete", [ApiUserCctvController::class, "destroy"]);
        });
        Route::get("/datatable", [ApiUserCctvController::class, "dataTable"]);
    });
});


// WEB API
Route::post("/auth/login/validate", [WebAuthController::class, "validateLogin"]);


Route::get("/custom_template/detail", [CustomTemplateController::class, "detail"]);
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

        // akses operator & operator cctv
        Route::get("/datatable", [WebBuildingController::class, "dataTable"]);
        Route::get("/list", [WebBuildingController::class, "list"]);
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

        // akses operator & operator cctv
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
            Route::post("/update-status", [WebCctvController::class, "updateStatus"]);
            Route::delete("/delete", [WebCctvController::class, "destroy"]);
        });

        // akses operator & operator cctv
        Route::get("/datatable", [WebCctvController::class, "dataTable"]);
        Route::get("/list", [WebCctvController::class, "list"]);
        Route::get("/{id}/detail", [WebCctvController::class, "getDetail"]);
        Route::post('/import-csv', [WebCctvController::class, 'importCsv']);
    });


    // User
    Route::prefix("user")->group(function () {
        // akses khusus superadmin
        Route::middleware("check.role:superadmin")->group(function () {
            Route::post("/create", [WebUserController::class, "create"]);
            Route::post("/update", [WebUserController::class, "update"]);
            Route::delete("/delete", [WebUserController::class, "destroy"]);
            Route::post("/update-status", [WebUserController::class, "updateStatus"]);
        });

        // akses operator & operator cctv
        Route::get("/datatable", [WebUserController::class, "dataTable"]);
        Route::get("/{id}/detail", [WebUserController::class, "getDetail"]);
    });

    // ACCOUNT
    Route::group(["prefix" => "account"], function () {
        Route::get("/detail", [WebAuthController::class, "detail"]);
        Route::post("/update", [WebAuthController::class, "update"]);
    });

    // User CCTV - only oprator cctv
    Route::group(["prefix" => "user-cctv"], function () {
        Route::middleware("check.role:superadmin")->group(function () {
            Route::post("/create", [WebUserCctvController::class, "create"]);
            Route::delete("/delete", [WebUserCctvController::class, "destroy"]);
            Route::post("/delete-by-data", [WebUserCctvController::class, "destroyByData"]);
        });
        Route::get("/datatable", [WebUserCctvController::class, "dataTable"]);
    });
});
