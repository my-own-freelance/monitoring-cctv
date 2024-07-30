<?php

namespace App\Http\Controllers\Web;

use App\Exports\CctvExport;
use App\Http\Controllers\Controller;
use App\Http\Services\CctvService;
use App\Models\Building;
use App\Models\Floor;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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

        if ($user->role == "operator_cctv") {
            $buildings = [];
            $floors = [];
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

    public function list(Request $request)
    {
        return $this->cctvService->list($request);
    }


    public function exportCsv(Request $request)
    {
        $filters = [
            'building_id' => $request->query("building_id"),
            'floor_id' => $request->query("floor_id"),
        ];

        return Excel::download(new CctvExport($filters), 'cctv_data.csv');
    }
}
