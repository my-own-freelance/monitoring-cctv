<?php

namespace App\Http\Services;

use App\Models\Building;
use App\Models\Floor;
use App\Models\User;
use App\Models\UserBuilding;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserService
{
    public function dataTable($request)
    {
        $query = User::select("id", "name", "username", "email", "role", "is_active");

        if ($request->query("search")) {
            $searchValue = $request->query("search")['value'];
            $query->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('username', 'like', '%' . $searchValue . '%');
            });
        }

        // filter role
        if ($request->query("role") && $request->query('role') != "") {
            $role = $request->query("role");
            $query->where(function ($query) use ($role) {
                $query->where('role', $role);
            });
        }

        // filter building_id
        if ($request->query("building_id") && $request->query('building_id') != "") {
            $userBuilding = UserBuilding::where("building_id", $request->query("building_id"))->pluck('user_id');
            $query->whereIn("id", $userBuilding);
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
                $action = "<div class='dropdown-primary dropdown open'>
                                <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                                    Aksi
                                </button>
                                <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                                    <a class='dropdown-item' onclick='return getData(\"{$item->id}\");' href='javascript:void(0);' title='Edit'>Edit</a>
                                    " . $action_delete . "
                                </div>
                            </div>";
            }

            $role = "";
            if ($item->role == "superadmin") {
                $role = "Super Admin";
            } else if ($item->role == "operator") {
                $role = "Operator";
            } else if ($item->role == "operator_gedung") {
                $role = "Operator Gedung";
            }

            $building = "-";
            if ($item->role == "operator_gedung") {
                $userBuilding = UserBuilding::with("building")->where("user_id", $item->id)->first();
                if ($userBuilding) {
                    $building = $userBuilding->building->name;
                }
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

            $account = '<small>
                            <strong>Username</strong> : ' . $item->username . '
                            <br>
                            <strong>Email</strong> : ' . $item->email . '
                            <br>
                        </small>';

            $item['action'] = $action;
            $item['role'] = $role;
            $item['building_access'] = $building;
            $item['is_active'] = $is_active;
            $item['account'] = $account;

            return $item;
        });

        $total = User::count();
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
            $user = User::select("id", "name", "username", "email", "role", "is_active")->find($id);

            if (!$user) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan",
                ], 404);
            }

            if ($user->role == "operator_gedung") {
                $userBuilding = UserBuilding::with("building")->where("user_id", $user->id)->first();
                if ($userBuilding) {
                    $user["building_id"] = $userBuilding->building->id;
                }
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
                "username" => "required|string|email|unique:users",
                "password" => "required|string|min:5",
                "role" => "required|string|in:superadmin,operator,operator_gedung",
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

            if ($request['role'] == "operator_gedung") {
                $rules['building_id'] = "required|integer";
                $messages['building_id.required'] = "Gedung harus diisi";
                $messages['building_id.integer'] = "Gedung tidak valid";
            }

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            $building = null;
            if ($request->role == "operator_gedung") {
                // cek building untuk role operator gedung
                $building = Building::find($request->building_id);
                if (!$building) {
                    return response()->json([
                        "status" => "error",
                        "message" => "Data Gedung tidak ditemukan"
                    ], 404);
                }
            }

            $user = new User();
            $user->name = $request->name;
            $user->username = $request->username;
            $user->password = Hash::make($request->password);
            $user->role = $request->role;
            $user->is_active = $request->is_active;
            $user->save();

            if ($request->role == "operator_gedung") {
                // buat user building untuk role operator gedung
                $data = [
                    "user_id" => $user->id,
                    "building_id" => $building->id
                ];
                UserBuilding::create($data);
            }

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
                "role" => "required|string|in:superadmin,operator,operator_gedung",
                "is_active" => "required|string|in:Y,N",
            ];
            if ($data['password'] != "") {
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

            if ($request['role'] == "operator_gedung") {
                $rules['building_id'] = "required|integer";
                $messages['building_id.required'] = "Gedung harus diisi";
                $messages['building_id.integer'] = "Gedung tidak valid";
            }

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

            $building = null;
            if ($request->role == "operator_gedung") {
                // cek building untuk role operator gedung
                $building = Building::find($data["building_id"]);
                if (!$building) {
                    return response()->json([
                        "status" => "error",
                        "message" => "Data Gedung tidak ditemukan"
                    ], 404);
                }

                $dataUserBuilding = [
                    "user_id" => $user->id,
                    "building_id" => $building->id
                ];
                // jika belum punya building. buat building baru
                // jika sudah pernah punya building. dan di set ke building baru, update saja data building lama
                $existingUserBuilding = UserBuilding::where("user_id", $user->id)->first();
                if (!$existingUserBuilding) {
                    UserBuilding::create($dataUserBuilding);
                } else if ($existingUserBuilding->building_id != $data["building_id"]) {
                    $existingUserBuilding->update($dataUserBuilding);
                }
            }

            // case jika role awal == operator gedung kemudian diubah jadi non operator gedung. hapus data user building
            if ($user->role == "operator_gedung" && $data["role"] != "operator_gedung") {
                UserBuilding::where("user_id", $user->id)->delete();
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
            // USING FOST DELETE, IMAGE TIDAK UDAH DI HAPUS
            // $oldImagePath = "public/" . $user->image;
            // if (Storage::exists($oldImagePath)) {
            //     Storage::delete($oldImagePath);
            // }

            $userBuilding = UserBuilding::where("user_id", $user->id)->first();
            if ($userBuilding) {
                $userBuilding->delete();
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
