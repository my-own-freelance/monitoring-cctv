<?php

namespace App\Http\Services;

use App\Models\Building;
use App\Models\Cctv;
use App\Models\Floor;
use App\Models\User;
use App\Models\UserCctv;
use Illuminate\Support\Facades\Validator;

class UserCctvService
{
    public function dataTable($request)
    {
        $query = UserCctv::with(['cctv' => function ($query) {
            // $query->select('id', 'name', 'floor_id', 'building_id')->with(['floor' => function ($query) {
            //     $query->select('id', 'name', 'building_id')->with(['building' => function ($query) {
            //         $query->select('id', 'name');
            //     }]);
            // }]);
        }])->select('id', 'user_id', 'cctv_id');

        // $query = UserCctv::query();
        // HANYA SUPERADMIN YG BISA MELIHAT DATA
        $user = auth()->user();
        if ($user->role != "superadmin") {
            return response()->json([
                'draw' => $request->query('draw'),
                'recordsFiltered' => 0,
                'recordsTotal' => 0,
                'data' => [],
            ]);
        }


        // filter user_id
        if ($request->query("user_id") && $request->query('user_id') != "") {
            $user_id = $request->query("user_id");
            $query->where(function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            });
        }

        // filter floor_id
        if ($request->query("floor_id") && $request->query('floor_id') != "") {
            $floor_id = $request->query("floor_id");
            $query->where(function ($query) use ($floor_id) {
                $query->where('floor_id', $floor_id);
            });
        }

        $recordsFiltered = $query->count();

        $data = $query->orderBy('created_at', 'desc')
            ->skip($request->query('start'))
            ->limit($request->query('length'))
            ->get();

        $output = $data->map(function ($item) {
            $action = "";
            $user = auth()->user();
            if ($user->role == "superadmin") {
                $action = "<div class='dropdown-primary dropdown open'>
                                <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                                    Aksi
                                </button>
                                <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                                    <a class='dropdown-item' onclick='return removeDataUserCctv(\"{$item->id}\");' href='javascript:void(0)' title='Hapus'>Hapus</a>
                                </div>
                            </div>";
            }
            $item['floor'] = "Data Terhapus";
            $item['building'] = "Data Terhapus";
            $cctvName = "Data Terhapus";
            if ($item['cctv']) {
                $cctvName = $item['cctv']['name'];
                $floor = Floor::select("name", "building_id")->find($item['cctv']['floor_id']);
                if ($floor) {
                    $item['floor'] = $floor['name'];
                    $building = Building::select("name")->find($floor['building_id']);
                    if ($building) {
                        $item['building'] = $building['name'];
                    }
                }
            }
            unset($item['cctv']);
            $item['cctv'] = $cctvName;
            $item['action'] = $action;
            return $item;
        });

        $total = UserCctv::count();
        return response()->json([
            'draw' => $request->query('draw'),
            'recordsFiltered' => $recordsFiltered,
            'recordsTotal' => $total,
            'data' => $output,
        ]);
    }

    public function create($request)
    {
        try {
            $data = $request->all();
            $rules = [
                "user_id" => "required|integer",
                "cctv_id" => "required|integer",
            ];

            $messages = [
                "user_id.required" => "Data User tidak valid",
                "user_id.integer" => "Data User tidak valid",
                "cctv_id.required" => "Data Cctv harus diisi",
                "cctv_id.integer" => "Data Cctv tidak valid",
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            // cek data user 
            $existingUser = User::where("id", $data["user_id"])->where("role", "operator_cctv")->first();
            if (!$existingUser) {
                return response()->json([
                    "status" => "error",
                    "message" => "User tidak tersedia / tidak valid"
                ], 404);
            }

            // cek data cctv
            $existingCctv = Cctv::find($data["cctv_id"]);
            if (!$existingCctv) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data cctv tidak tersedia"
                ], 404);
            }

            // cek existing user cctv
            $userCctv = UserCctv::where("user_id", $data["user_id"])->where("cctv_id", $data["cctv_id"])->first();
            if ($userCctv) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data sudah tersedia"
                ], 404);
            }

            UserCctv::create($data);
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
            $userCctv = UserCctv::find($id);
            if (!$userCctv) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan"
                ], 404);
            }

            $userCctv->delete();
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
}
