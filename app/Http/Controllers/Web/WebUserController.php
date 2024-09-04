<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Services\UserService;
use App\Models\Building;
use App\Models\Cctv;
use Illuminate\Http\Request;

class WebUserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $title = "Data Pengguna";
        $buildings = Building::orderBy("name", "asc")->get();
        $cctvs = Cctv::orderBy("name", "asc")->get();
        $user = Auth()->user();
        if ($user->role == "operator_cctv") {
            return redirect()->route("dashboard");
        }
        return view("pages.admin.user", compact('title', 'buildings', 'cctvs', 'user'));
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
