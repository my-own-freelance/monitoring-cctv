<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Services\UserService;
use Illuminate\Http\Request;

class ApiUserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    // HANDLER API
    public function dataTable(Request $request)
    {
        return $this->userService->dataTable($request);
    }

    public function getDetail($id)
    {
        return $this->userService->getDetail($id);
    }

    public function create(Request $request)
    {
        return $this->userService->create($request);
    }

    public function update(Request $request)
    {
        return $this->userService->update($request);
    }

    public function destroy(Request $request)
    {
        return $this->userService->destroy($request);
    }

    public function updateStatus(Request $request)
    {
        return $this->userService->updateStatus($request);
    }
}
