<?php

namespace App\Http\Services;

use App\Models\CustomTemplate;
use Illuminate\Support\Facades\Storage;

class CustomTemplateService
{
    public function detail()
    {
        try {
            $customTemplate = CustomTemplate::find(1);

            if (!$customTemplate) {
                return response()->json([
                    "status" => "success",
                    "message" => "Template is not set"
                ], 404);
            }

            if (!$customTemplate->web_title) {
                $customTemplate["web_title"] = "CCTV";
            }

            if (!$customTemplate->web_description) {
                $customTemplate["web_description"] = "Dashboard pengelolaan data monitoring CCTV bangunan, lantai untuk monitoring CCTV real-time di mobile apps";
            }

            if ($customTemplate->web_logo) {
                $customTemplate['web_logo'] =  url("/") . Storage::url($customTemplate->web_logo);
            } else {
                $customTemplate['web_logo'] = url("/") . "/dashboard/icon/icon.png";
            }

            return response()->json([
                "status" => "success",
                "data" => $customTemplate
            ]);
        } catch (\Exception $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage(),
            ], 500);
        }
    }

    public function saveUpdateData($request)
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

        unset($data["web_logo"]);
        if ($request->file("web_logo")) {
            $oldImagePath = "public/" . $existCustomData->web_logo;
            if (Storage::exists($oldImagePath)) {
                Storage::delete($oldImagePath);
            }
            $data["web_logo"] = $request->file("web_logo")->store("assets/setting", "public");
        }

        $existCustomData->update($data);
        return response()->json([
            "status" => 200,
            "message" => "Warna template berhasil diubah"
        ]);
    }
}
