<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Services\BuildingService;
use Illuminate\Http\Request;

class WebBuildingController extends Controller
{
    protected $buildingService;

    public function __construct(BuildingService $buildingService)
    {
        $this->buildingService = $buildingService;
    }

    public function index()
    {
        $title = "Data Gedung";
        return view("pages.admin.building", compact('title'));
    }

    // HANDLER API
    public function dataTable(Request $request)
    {
        return $this->buildingService->dataTable($request);
    }

    public function getDetail($id)
    {
        return $this->buildingService->getDetail($id);
    }

    public function create(Request $request)
    {
        return $this->buildingService->create($request);
    }

    public function update(Request $request)
    {
        return $this->buildingService->update($request);
    }

    public function destroy(Request $request)
    {
        return $this->buildingService->destroy($request);
    }
}
