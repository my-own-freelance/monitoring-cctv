<?php

namespace App\Http\Services;

use App\Models\Cctv;
use App\Models\UserCctv;
use Illuminate\Support\Facades\Validator;

class UserCctvService
{
    public function dataTable($request)
    {
        $query = UserCctv::with(["cctv" => function ($query) {
            $query::with(["building" => function ($query) {
                $query->select("id", "name");
            }])->select("id", "name");
        }]);

        // HANYA OPERATOR CCTV YG BISA MELIHAT DATA
        $user = auth()->user();
        if ($user->role != "operator_cctv") {
            return response()->json([
                'draw' => $request->query('draw'),
                'recordsFiltered' => 0,
                'recordsTotal' => 0,
                'data' => [],
            ]);
        }

        $recordsFiltered = $query->count();

        $data = $query->orderBy('created_at', 'desc')
            ->skip($request->query('start'))
            ->limit($request->query('length'))
            ->get();

        $output = $data->map(function ($item) {
            $action = "";
            $user = auth()->user();
            if ($user->role == "operator_cctv") {
                $action = "<div class='dropdown-primary dropdown open'>
                                <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                                    Aksi
                                </button>
                                <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                                    <a class='dropdown-item' onclick='return removeData(\"{$item->id}\");' href='javascript:void(0)' title='Hapus'>Hapus</a>
                                </div>
                            </div>";
            }

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

            // cek data cctv
            $existingCctv = Cctv::find($data["cctv_id"]);
            if ($existingCctv) {
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
