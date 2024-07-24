<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Services\FloorService;
use Illuminate\Http\Request;

class ApiFloorController extends Controller
{
    protected $floorService;

    public function __construct(FloorService $floorService)
    {
        $this->floorService = $floorService;
    }

    // HANDLER API
    public function dataTable(Request $request)
    {
        return $this->floorService->dataTable($request);
    }

    public function getDetail($id)
    {
        return $this->floorService->getDetail($id);
    }

    public function create(Request $request)
    {
        return $this->floorService->create($request);
    }

    public function update(Request $request)
    {
        return $this->floorService->update($request);
    }

    public function destroy(Request $request)
    {
        return $this->floorService->destroy($request);
    }

    public function list(Request $request)
    {
        return $this->floorService->list($request);
    }
}
