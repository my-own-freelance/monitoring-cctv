<?php

namespace App\Http\Services;

use App\Models\User;
use App\Models\UserCctv;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserService
{
    public function dataTable($request)
    {
        $query = User::select("id", "name", "username", "email", "role", "is_active", "device_token")
            ->whereNull('is_master')
            ->orWhere('is_master', false);

        if ($request->query("search")) {
            $searchValue = $request->query("search")['value'];
            $query->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('username', 'like', '%' . $searchValue . '%')
                    ->orWhere('device_token', 'like', '%' . $searchValue . '%');
            });
        }

        // filter role
        if ($request->query("role") && $request->query('role') != "") {
            $role = $request->query("role");
            $query->where(function ($query) use ($role) {
                $query->where('role', $role);
            });
        }


        $recordsFiltered = $query->count();

        $data = $query->orderBy('name', 'asc')
            ->skip($request->query('start'))
            ->limit($request->query('length'))
            ->get();

        $output = $data->map(function ($item) {
            $action = "";

            // SUPERADMIN BISA LIHAT SEMUA DATA DAN KELOLA DATA
            $user = auth()->user();
            if ($user->role == "superadmin") {
                $action_delete = $user->id != $item->id ? "<a class='dropdown-item' onclick='return removeData(\"{$item->id}\");' href='javascript:void(0)' title='Hapus'>Hapus</a>" : "";
                $action_add_cctv = $item->role == "operator_cctv" ? "<a class='dropdown-item' onclick='return addCctv(\"{$item->id}\");' href='javascript:void(0)' title='Tambah CCTV'>Atur CCTC</a>" : "";
                $action = "<div class='dropdown-primary dropdown open'>
                                <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                                    Aksi
                                </button>
                                <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                                    <a class='dropdown-item' onclick='return getData(\"{$item->id}\");' href='javascript:void(0);' title='Edit'>Edit</a>
                                    " . $action_add_cctv . "
                                    " . $action_delete . "
                                </div>
                            </div>";
            }

            $role = "";
            if ($item->role == "superadmin") {
                $role = "Super Admin";
            } else if ($item->role == "operator") {
                $role = "Operator";
            } else if ($item->role == "operator_cctv") {
                $role = "Operator CCTV";
            }

            $is_active = $item->is_active == 'Y' ? '
                <div class="text-center">
                    <span class="label-switch">Active</span>
                </div>
                <div class="input-row">
                    <div class="toggle_status on">
                        <input type="checkbox" onclick="return updateStatus(\'' . $item->id . '\', \'Disabled\');" />
                        <span class="slider"></span>
                    </div>
                </div>' :
                '
                <div class="text-center">
                    <span class="label-switch">Disabled</span>
                </div>
                <div class="input-row">
                    <div class="toggle_status off">
                        <input type="checkbox" onclick="return updateStatus(\'' . $item->id . '\', \'Active\');" />
                        <span class="slider"></span>
                    </div>
                </div>';

            $item['action'] = $action;
            $item['role'] = $role;
            $item['is_active'] = $is_active;

            return $item;
        });

        $total = User::whereNull('is_master')
            ->orWhere('is_master', false)->count();
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
            $user = User::select("id", "name", "username", "email", "role", "is_active", "device_token")->find($id);

            if (!$user) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan",
                ], 404);
            }

            return response()->json([
                "status" => "success",
                "data" => $user
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
            $rules = [
                "name" => "required|string",
                "username" => "required|string|unique:users",
                "email" => "required|string|email|unique:users",
                "password" => "required|string|min:5",
                "role" => "required|string|in:superadmin,operator,operator_cctv",
                "is_active" => "required|string|in:Y,N",
            ];

            $messages = [
                "name.required" => "Nama harus diisi",
                "username.required" => "Username harus diisi",
                "username.unique" => "Username sudah digunakan",
                "email.required" => "Email harus diisi",
                "email.unique" => "Email sudah digunakan",
                "email.email" => "Email tidak valid",
                "password.required" => "Password harus diisi",
                "password.min" => "Password minimal 5 karakter",
                "role.required" => "Level harus diisi",
                "role.in" => "Level tidak sesuai",
                "is_active" => "Status harus diisi",
                "is_active.in" => "Status tidak sesuai",
            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->username = $request->username;
            $user->password = Hash::make($request->password);
            $user->role = $request->role;
            $user->is_active = $request->is_active;
            $user->save();

            return response()->json([
                "status" => "success",
                "message" => "Berhasil menambahkan data pengguna"
            ]);
        } catch (\Exception $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage()
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
                "email" => "required|string|email",
                "password" => "nullable",
                "role" => "required|string|in:superadmin,operator,operator_cctv",
                "is_active" => "required|string|in:Y,N",
            ];
            if ($data && $data['password'] != "") {
                $rules['password'] .= "|string|min:5";
            }

            $messages = [
                "id.required" => "Data ID harus diisi",
                "id.integer" => "Type ID tidak sesuai",
                "name.required" => "Nama harus diisi",
                "email.required" => "Email harus diisi",
                "email.email" => "Email tidak valid",
                "password.min" => "Password minimal 5 karakter",
                "role.required" => "Level harus diisi",
                "role.in" => "Level tidak sesuai",
                "is_active" => "Status harus diisi",
                "is_active.in" => "Status tidak sesuai",
            ];


            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            $user = User::find($data['id']);

            if (!$user) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data pengguna tidak ditemukan"
                ], 404);
            }

            if ($data['password'] && $data['password'] != "") {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            // agar username tidak bisa diganti
            if ($data['username']) {
                unset($data['username']);
            }

            // jika email di update
            $existingEmail = User::where("email", $data['email'])->where('id', '!=', $user->id)->first();
            if ($existingEmail) {
                return response()->json([
                    "status" => "error",
                    "message" => "Email sudah digunakan"
                ], 404);
            }

            // update device token
            if ($data["device_token"] == "") {
                $data["device_token"] = null;
            }

            $user->update($data);

            return response()->json([
                "status" => "success",
                "message" => "Berhasil update data pengguna"
            ]);
        } catch (\Exception $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage()
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
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan"
                ], 404);
            }

            $userCctv = UserCctv::where("user_id", $user->id)->first();
            if ($userCctv) {
                $userCctv->delete();
            }

            $user->delete();
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

    public function updateStatus($request)
    {
        try {
            $data = $request->all();
            $rules = [
                "id" => "required|integer",
                "is_active" => "required|in:N,Y",
            ];

            $messages = [
                "id.required" => "Data ID harus diisi",
                "id.integer" => "Type ID tidak sesuai",
                "is_active.required" => "Status harus diisi",
                "is_active.in" => "Status tidak sesuai",
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            $user = User::find($data['id']);
            if (!$user) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data pengguna tidak ditemukan"
                ], 404);
            }
            $user->update($data);
            return response()->json([
                "status" => "success",
                "message" => "Status berhasil diperbarui"
            ]);
        } catch (\Exception $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage(),
            ], 500);
        }
    }
}
