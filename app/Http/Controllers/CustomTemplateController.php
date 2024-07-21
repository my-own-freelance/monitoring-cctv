<?php

namespace App\Http\Controllers;

use App\Models\CustomTemplate;
use Illuminate\Http\Request;

class CustomTemplateController extends Controller
{
    public function saveUpdateData(Request $request)
    {
        $data = $request->all();
        $existCustomData = CustomTemplate::find(1);
        if (!$existCustomData) {
            CustomTemplate::create($data);
            return response()->json([
                "status" => 200,
                "message" => "Warna template berhasil diubah"
            ]);
        }

        $existCustomData->update($data);
        return response()->json([
            "status" => 200,
            "message" => "Warna template berhasil diubah"
        ]);
    }
}
