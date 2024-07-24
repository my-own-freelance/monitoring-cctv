<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Services\CctvService;
use App\Models\Building;
use App\Models\Floor;
use App\Models\UserBuilding;
use Illuminate\Http\Request;

class WebCctvController extends Controller
{
    protected $cctvService;

    public function __construct(CctvService $cctvService)
    {
        $this->cctvService = $cctvService;
    }

    public function index()
    {
        $title = "Data CCTV";
        $buildings = Building::all();
        $floors = Floor::all();
        $user = Auth()->user();

        // UNTUK KEBUTUHAN FILTER DATA TABEL KETIKA DI AKSES OLEH OPERATOR GEDUNG
        if ($user->role == "operator_gedung") {
            $userBuilding = UserBuilding::where("user_id", $user->id)->first();
            if (!$userBuilding) {
                $buildings = [];
                $floors = [];
            } else {
                $buildings = Building::where("id", $userBuilding->building_id)->get();
                $floors = Floor::where("building_id", $userBuilding->building_id)->get();
            }
        }
        return view("pages.admin.cctv", compact('title', 'buildings', 'floors', 'user'));
    }

    // HANDLER API
    public function dataTable(Request $request)
    {
        return $this->cctvService->dataTable($request);
    }

    public function getDetail($id)
    {
        return $this->cctvService->getDetail($id);
    }

    public function create(Request $request)
    {
        return $this->cctvService->create($request);
    }

    public function update(Request $request)
    {
        return $this->cctvService->update($request);
    }

    public function destroy(Request $request)
    {
        return $this->cctvService->destroy($request);
    }
}
