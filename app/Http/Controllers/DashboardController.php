<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Cctv;
use App\Models\Floor;
use App\Models\User;
use App\Models\UserBuilding;
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

        if ($user->role == "operator_gedung") {
            $userBuilding = UserBuilding::with("building")->where("user_id", $user->id)->first();
            if ($userBuilding) {
                $buildingName = $userBuilding->building->name;
                $floor = Floor::where("building_id", $userBuilding->building->id)->count();
            }
        }

        return view("pages.admin.index", compact("title", "buildings", "floors", "cctvs", "users", "buildingName", "user"));
    }
}
