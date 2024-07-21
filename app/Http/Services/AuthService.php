<?php

namespace App\Http\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthService
{
    public function register($data)
    {
        try {
            $rules = [
                "name" => "required|string",
                "username" => "required|string|unique:users",
                "password" => "required|string|min:5",
                "passwordConfirm" => "required|string|same:password"
            ];

            $messages = [
                "name.required" => "Nama harus diisi",
                "username.required" => "Username harus diisi",
                "username.unique" => "Username sudah digunakan",
                "password.required" => "Password harus diisi",
                "password.min" => "Password minimal 5 karakter",
                "passwordConfirm.required" => "Password harus diisi",
                "passwordConfirm.same" => "Password Confirm tidak sesuai"
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            $user = new User();
            $user->name = $data->name;
            $user->username = $data->username;
            $user->password = Hash::make($data->password);
            $user->role = "operator_gedung";
            $user->is_active = "Y";
            $user->save();

            return response()->json([
                "status" => "success",
                "message" => "Registrasi berhasil"
            ]);
        } catch (\Exception $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage()
            ], 500);
        }
    }
}
