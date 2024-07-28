<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Cctv;
use App\Models\Floor;
use App\Models\User;
use App\Models\UserBuilding;
use App\Models\UserCctv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class DashboardController extends Controller
{
    public function index()
    {
        $title = "Dashboard Monitoring CCTV";
        $user = Auth()->user();
        $buildings = Building::count();
        $floors = Floor::count();
        $cctvs = Cctv::count();
        $users = User::count();
        $buildingName = "NOT ASSIGN";

        if ($user->role == "operator_cctv") {
            $buildings = 0;
            $floors = 0;
            $cctvs = UserCctv::where("user_id", $user->id)->count();
        }

        return view("pages.admin.index", compact("title", "buildings", "floors", "cctvs", "users", "buildingName", "user"));
    }
}
