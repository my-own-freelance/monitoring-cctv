<?php

namespace App\Http\Services;

use App\Models\Building;
use App\Models\Cctv;
use App\Models\Floor;
use App\Models\UserBuilding;
use Illuminate\Support\Facades\Storage;
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
                    ->orWhere('url', 'like', '%' . $searchValue . '%')
                    ->orWhere('description', 'like', '%' . $searchValue . '%');
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

        // OPERATOR GEDUNG BISA LIHAT DATA SESUI GEDUNG DIA
        $user = auth()->user();
        if ($user->role == "operator_gedung") {
            $userBuilding = UserBuilding::where('user_id', $user->id)->first();
            if (!$userBuilding) {
                return response()->json([
                    'draw' => $request->query('draw'),
                    'recordsFiltered' => 0,
                    'recordsTotal' => 0,
                    'data' => [],
                ]);
            }
            $query->where("building_id", $userBuilding->building_id);
        }

        $recordsFiltered = $query->count();

        $data = $query->orderBy('created_at', 'desc')
            ->skip($request->query('start'))
            ->limit($request->query('length'))
            ->get();

        $output = $data->map(function ($item) {
            $action = "";

            // SUPERADMIN BISA LIHAT SEMUA DATA DAN KELOLA DATA
            // OPERATOR HANYA BISA LIHAT SEMUA DATA
            // OPERATOR GEDUNG BISA LIHAT DATA SESUI GEDUNG DIA
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

            $image = '<div class="thumbnail">
                        <div class="thumb">
                            <img src="' . Storage::url($item->image) . '" width="200px" height="200px" 
                            class="img-fluid img-thumbnail" alt="' . $item->name . '">
                        </div>
                    </div>';

            $area = '<small>
                        <strong>Gedung</strong> : ' . $item->building->name . '
                        <br>
                        <strong>Lantai</strong> : ' . $item->floor->name . '
                        <br>
                        <strong>Url CCTV </strong>: ' . $item->url . '
                    </small>';
            $item['action'] = $action;
            $item['mobile_image'] = url("/") . Storage::url($item->image);
            $item['image'] = $image;
            $item['area'] = $area;
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
                "description" => "required|string",
                "image" => "required|image|max:1024|mimes:giv,svg,jpeg,png,jpg",
                "building_id" => "required|integer",
                "floor_id" => "required|integer",
            ];

            $messages = [
                "name.required" => "Judul harus diisi",
                "url.required" => "Url CCTV harus diisi",
                "description.required" => "Deskripsi harus diisi",
                "image.required" => "Gambar harus di isi",
                "image.image" => "Gambar yang di upload tidak valid",
                "image.max" => "Ukuran gambar maximal 1MB",
                "image.mimes" => "Format gambar harus giv/svg/jpeg/png/jpg",
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

            if ($request->file('image')) {
                $data['image'] = $request->file('image')->store('assets/cctv', 'public');
            }

            Cctv::create($data);
            return response()->json([
                "status" => "success",
                "message" => "Data berhasil dibuat"
            ]);
        } catch (\Exception $err) {
            if ($request->file("image")) {
                unlink(public_path("storage/assets/cctv/" . $request->image->hashName()));
            }
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
                "description" => "required|string",
                "image" => "nullable",
                "building_id" => "required|integer",
                "floor_id" => "required|integer",
            ];

            if ($request->file('image')) {
                $rules['image'] .= '|image|max:1024|mimes:giv,svg,jpeg,png,jpg';
            }

            $messages = [
                "id.required" => "Data ID harus diisi",
                "id.integer" => "Type ID tidak sesuai",
                "name.required" => "Judul harus diisi",
                "description.required" => "Deskripsi harus diisi",
                "image.image" => "Gambar yang di upload tidak valid",
                "image.max" => "Ukuran gambar maximal 1MB",
                "image.mimes" => "Format gambar harus giv/svg/jpeg/png/jpg",
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

            // delete undefined data image
            unset($data["image"]);
            if ($request->file("image")) {
                $oldImagePath = "public/" . $cctv->image;
                if (Storage::exists($oldImagePath)) {
                    Storage::delete($oldImagePath);
                }
                $data["image"] = $request->file("image")->store("assets/cctv", "public");
            }

            $cctv->update($data);
            return response()->json([
                "status" => "success",
                "message" => "Data berhasil diperbarui"
            ]);
        } catch (\Exception $err) {
            if ($request->file("image")) {
                unlink(public_path("storage/assets/cctv/" . $request->image->hashName()));
            }
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
            // USING FOST DELETE, IMAGE TIDAK UDAH DI HAPUS
            // $oldImagePath = "public/" . $cctv->image;
            // if (Storage::exists($oldImagePath)) {
            //     Storage::delete($oldImagePath);
            // }

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
}
