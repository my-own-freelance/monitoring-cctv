<?php

namespace App\Http\Services;

use App\Models\Building;
use App\Models\Cctv;
use App\Models\Floor;
use App\Models\UserCctv;
use Illuminate\Support\Facades\Validator;

class CctvService
{
    public function dataTable($request)
    {
        $query = Cctv::with(["floor" => function ($query) {
            $query->select("id", "name");
        }])->with(["building" => function ($query) {
            $query->select("id", "name");
        }]);

        if ($request->query("search")) {
            $searchValue = $request->query("search")['value'];
            $query->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('url', 'like', '%' . $searchValue . '%');
            });
        }

        // filter building_id
        if ($request->query("building_id") && $request->query('building_id') != "") {
            $building_id = $request->query("building_id");
            $query->where(function ($query) use ($building_id) {
                $query->where('building_id', $building_id);
            });
        }

        // filter floor_id
        if ($request->query("floor_id") && $request->query('floor_id') != "") {
            $floor_id = $request->query("floor_id");
            $query->where(function ($query) use ($floor_id) {
                $query->where('floor_id', $floor_id);
            });
        }

        // OPERATOR CCTV BISA LIHAT DATA SESUI CCTV DIA
        $user = auth()->user();
        if ($user->role == "operator_cctv") {
            $userCctv = UserCctv::where('user_id', $user->id)->pluck("cctv_id");
            $query->whereIn("id", $userCctv);
        }

        $recordsFiltered = $query->count();

        $data = $query->orderBy('id', 'desc')
            ->skip($request->query('start'))
            ->limit($request->query('length'))
            ->get();

        $output = $data->map(function ($item) {
            $action = "#";

            // SUPERADMIN BISA LIHAT SEMUA DATA DAN KELOLA DATA
            // OPERATOR HANYA BISA LIHAT SEMUA DATA
            // OPERATOR CCTV BISA LIHAT DATA SESUI CCTV DIA
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

            if($user->role != "superadmin"){
                $item['url'] = "#";
            }

            $item['action'] = $action;
            $floor = $item['floor'];
            $building = $item['building'];
            unset($item['floor']);
            unset($item['building']);
            $item['floor'] = $floor ? $floor['name'] : "Data Terhapus";
            $item['building'] = $building ? $building['name'] : "Data Terhapus";
            return $item;
        });

        $total = Cctv::count();
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
            $cctv = Cctv::find($id);

            if (!$cctv) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan",
                ], 404);
            }

            return response()->json([
                "status" => "success",
                "data" => $cctv
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
                "url" => "required|string",
                "building_id" => "required|integer",
                "floor_id" => "required|integer",
            ];

            $messages = [
                "name.required" => "Nama CCTV harus diisi",
                "url.required" => "Url CCTV harus diisi",
                "building_id.required" => "Data Gedung harus diisi",
                "building_id.integer" => "Data Gedung tidak valid",
                "floor_id.required" => "Data Lantai harus diisi",
                "floor_id.integer" => "Data Lantai tidak valid",
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

            // cek existing floor
            $floor = Floor::where('id', $data['floor_id'])
                ->where('building_id', $data['building_id'])
                ->first();
            if (!$floor) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data Lantai tidak ditemukan"
                ], 404);
            }

            Cctv::create($data);
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
                "floor_id" => "required|integer",
            ];

            $messages = [
                "id.required" => "Data ID harus diisi",
                "id.integer" => "Type ID tidak sesuai",
                "name.required" => "Nama CCTV harus diisi",
                "building_id.required" => "Data Gedung harus diisi",
                "building_id.integer" => "Data Gedung tidak valid",
                "floor_id.required" => "Data Lantai harus diisi",
                "floor_id.integer" => "Data Lantai tidak valid",
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                    "data" => $request->id
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

            // cek existing floor
            $floor = Floor::where('id', $data['floor_id'])
                ->where('building_id', $data['building_id'])
                ->first();
            if (!$floor) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data Lantai tidak ditemukan"
                ], 404);
            }

            $cctv = Cctv::find($data['id']);
            if (!$cctv) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan"
                ], 404);
            }

            $cctv->update($data);
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
            $cctv = Cctv::find($id);
            if (!$cctv) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan"
                ], 404);
            }

            $userCctv = UserCctv::where("cctv_id", $cctv->id)->first();
            if ($userCctv) {
                $userCctv->delete();
            }

            $cctv->delete();
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
            $query = Cctv::with(["floor" => function ($query) {
                $query->select("id", "name");
            }]);

            // jika tidak di filter by floor id, limit 100 data saja yg ditampilkan secara random
            if ($request->has("floor_id") && is_numeric($request->query('floor_id'))) {
                $floor_id = $request->query("floor_id");
                $query->where('floor_id', $floor_id);
            } else {
                // $query->limit(100);
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
