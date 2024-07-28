<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Services\CctvService;
use Illuminate\Http\Request;

class ApiCctvController extends Controller
{
    protected $cctvService;

    public function __construct(CctvService $cctvService)
    {
        $this->cctvService = $cctvService;
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
}
