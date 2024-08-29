<?php

namespace App\Http\Services;

use App\Models\Building;
use App\Models\Cctv;
use App\Models\Floor;
use Illuminate\Support\Facades\Validator;

class FloorService
{
    public function dataTable($request)
    {
        $query = Floor::with(["building" => function ($query) {
            $query->select("id", "name");
        }]);

        if ($request->query("search")) {
            $searchValue = $request->query("search")['value'];
            $query->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%');
            });
        }

        // filter building_id
        if ($request->query("building_id") && $request->query('building_id') != "") {
            $building_id = $request->query("building_id");
            $query->where(function ($query) use ($building_id) {
                $query->where('building_id', $building_id);
            });
        }

        // OPERATOR CCTV TIDAK BISA LIHAT DATA
        $user = auth()->user();
        if ($user->role == "operator_cctv") {
            return response()->json([
                'draw' => $request->query('draw'),
                'recordsFiltered' => 0,
                'recordsTotal' => 0,
                'data' => [],
            ]);
        }

        $recordsFiltered = $query->count();

        $data = $query->orderBy('id', 'desc')
            ->skip($request->query('start'))
            ->limit($request->query('length'))
            ->get();

        $output = $data->map(function ($item) {
            $action = "";

            // SUPERADMIN BISA LIHAT SEMUA DATA DAN KELOLA DATA
            // OPERATOR HANYA BISA LIHAT SEMUA DATA
            // OPERATOR CCTV TIDAK BISA LIHAT DATA
            $user = auth()->user();
            if ($user->role == "superadmin") {
                $action = "<div class='dropdown-primary dropdown open'>
                                <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                                    Aksi
                                </button>
                                <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                                    <a class='dropdown-item' onclick='return getData(\"{$item->id}\");' href='javascript:void(0);' title='Edit'>Edit</a>
                                    <a class='dropdown-item' onclick='return removeData(\"{$item->id}\");' href='javascript:void(0)' title='Hapus'>Hapus</a>
                                </div>
                            </div>";
            }

            $item['action'] = $action;
            $building = $item['building'];
            unset($item['building']);
            $item['building'] = $building ? $building['name'] : "Data Terhapus";
            return $item;
        });

        $total = Floor::count();
        return response()->json([
            'draw' => $request->query('draw'),
            'recordsFiltered' => $recordsFiltered,
            'recordsTotal' => $total,
            'data' => $output,
        ]);
    }

    public function getDetail($id)
    {
        try {
            $floor = Floor::with('building')->find($id);

            if (!$floor) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan",
                ], 404);
            }

            return response()->json([
                "status" => "success",
                "data" => $floor
            ]);
        } catch (\Exception $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage()
            ], 500);
        }
    }

    public function create($request)
    {
        try {
            $data = $request->all();
            $rules = [
                "name" => "required|string",
                "building_id" => "required|integer",
            ];

            $messages = [
                "name.required" => "Nama Lantai harus diisi",
                "building_id.required" => "Data Gedung harus diisi",
                "building_id.integer" => "Data tidak valid diisi"
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            // cek existing building
            $building = Building::find($data['building_id']);
            if (!$building) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data Gedung tidak ditemukan"
                ], 404);
            }

            Floor::create($data);
            return response()->json([
                "status" => "success",
                "message" => "Data berhasil dibuat"
            ]);
        } catch (\Exception $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage(),
            ], 500);
        }
    }

    public function update($request)
    {
        try {
            $data = $request->all();
            $rules = [
                "id" => "required|integer",
                "name" => "required|string",
                "building_id" => "required|integer",
            ];

            $messages = [
                "id.required" => "Data ID harus diisi",
                "id.integer" => "Type ID tidak sesuai",
                "name.required" => "Nam Lantai harus diisi",
                "building_id.required" => "Data Gedung harus diisi",
                "building_id.integer" => "Data tidak valid diisi"
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                    "data" => $request->id
                ], 400);
            }

            $floor = Floor::find($data['id']);
            if (!$floor) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan"
                ], 404);
            }

            // cek existing building
            $building = Building::find($data['building_id']);
            if (!$building) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data Gedung tidak ditemukan"
                ], 404);
            }

            // If the building_id on the floor is changed, please also change the building_id on the CCTV so that the data is in sync
            if ($floor->building_id != $data["building_id"]) {
                $cctvUpdate = ["building_id" =>  $data["building_id"]];
                Cctv::where("floor_id", $floor->id)->update($cctvUpdate);
            }

            $floor->update($data);
            return response()->json([
                "status" => "success",
                "message" => "Data berhasil diperbarui"
            ]);
        } catch (\Exception $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage(),
            ], 500);
        }
    }

    public function destroy($request)
    {
        try {
            $validator = Validator::make($request->all(), ["id" => "required|integer"], [
                "id.required" => "Data ID harus diisi",
                "id.integer" => "Type ID tidak valid"
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first()
                ], 400);
            }

            $id = $request->id;
            $floor = Floor::find($id);
            if (!$floor) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan"
                ], 404);
            }

            $floor->delete();
            return response()->json([
                "status" => "success",
                "message" => "Data berhasil dihapus"
            ]);
        } catch (\Exception $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage()
            ], 500);
        }
    }

    public function list($request)
    {
        try {
            $query = Floor::with(["building" => function ($query) {
                $query->select("id", "name");
            }]);

            // jika tidak di filter by building id, limit 100 data saja yg ditampilkan secara random
            if ($request->has("building_id")) {
                $building_id = $request->query("building_id");
                $query->where('building_id', $building_id);
            } else {
                $query->limit(100);
            }

            $data = $query->orderBy("name", "asc")->get();
            return response()->json([
                "status" => "success",
                "data" => $data
            ]);
        } catch (\Exception $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage()
            ], 500);
        }
    }
}
