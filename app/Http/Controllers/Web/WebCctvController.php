<?php

namespace App\Http\Controllers\Web;

use App\Exports\CctvExport;
use App\Http\Controllers\Controller;
use App\Http\Services\CctvService;
use App\Imports\CctvImport;
use App\Models\Building;
use App\Models\Floor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
        $buildings = Building::orderBy("name", "asc")->get();
        $floors = Floor::orderBy("name", "asc")->get();
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

    public function updateStatus(Request $request)
    {
        return $this->cctvService->updateStatus($request);
    }

    public function exportCsv(Request $request)
    {
        $filters = [
            'building_id' => $request->query("building_id"),
            'floor_id' => $request->query("floor_id"),
        ];

        return Excel::download(new CctvExport($filters), 'cctv_data.csv');
    }

    public function importCsv(Request $request)
    {
        $rules = [
            "file" => 'required|mimes:csv|max:10240',
        ];

        $messages = [
            "file.required" => "File import harus diisi",
            "file.max" => "Ukuran gambar maximal 10MB",
            "file.mimes" => "Format file harus .csv"
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => $validator->errors()->first(),
            ], 400);
        }

        Excel::import(new CctvImport, $request->file('file'));

        return response()->json([
            "status" => "success",
            "message" => "Data Berhasil di Import"
        ]);
    }
}
