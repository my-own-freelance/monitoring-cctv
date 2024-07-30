<?php

namespace App\Http\Controllers;

use App\Http\Services\AuthService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class WebAuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        $title = "Kelola Akses";
        return view("pages.auth.index", compact("title"));
    }

    public function account()
    {
        $title = "Kelola Akun";
        return view("pages.admin.account", compact("title"));
    }

    // API
    public function register(Request $request)
    {
        return $this->authService->register($request, "web");
    }

    public function validateLogin(Request $request)
    {
        try {
            $rules = [
                "username" => "required|string",
                "password" => "required|string",
            ];

            $messages = [
                "username.required" => "Username harus diisi",
                "password.required" => "Password harus diisi"
            ];

            $validate = Validator::make($request->all(), $rules, $messages);
            if ($validate->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validate->errors()->first(),
                ], 400);
            }

            $user = User::where('username', $request->username)->first();

            if ($user && $user->is_active != "Y") {
                return response()->json([
                    "status" => "error",
                    "message" => "Akun tidak aktif"
                ], 400);
            }

            if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
                $data = User::where('username', $request->username)
                    ->select('name', 'username', 'role', 'is_active', 'is_master')
                    ->first();

                // $request->session()->put('user', $data);
                Cookie::queue("user", $data);
                return response()->json([
                    "status" => "success",
                    "message" => "Login Sukses",
                ]);
            }

            return response()->json([
                "status" => "error",
                "message" => "Username / Password salah"
            ], 400);
        } catch (\Exception $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage()
            ], 500);
        }
    }
    public function logout(Request $request)
    {
        // $request->session()->forget('user');
        Auth::logout();
        Cookie::queue(Cookie::forget("user"));

        return redirect()->route('login');
    }

    public function detail()
    {
        return $this->authService->detail();
    }

    public function update(Request $request)
    {
        return $this->authService->update($request);
    }
}
