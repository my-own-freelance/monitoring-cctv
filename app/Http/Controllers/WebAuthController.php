<?php

namespace App\Http\Controllers;

use App\Http\Services\AuthService;
use App\Models\CustomTemplate;
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
        $setting = CustomTemplate::find(1);
        $description = "Dashboard pengelolaan data monitoring CCTV bangunan, lantai untuk monitoring CCTV real-time di mobile apps";
        if ($setting && $setting->web_description) {
            $description = $setting->web_description;
        }
        return view("pages.auth.index", compact("title", 'description'));
    }

    public function account()
    {
        $title = "Kelola Akun";
        return view("pages.admin.account", compact("title"));
    }

    // API
    public function register(Request $request)
    {
        return $this->authService->register($request);
    }

    function validateCaptcha($captchaResponse)
    {
        $secretKey = env('recaptcha2.secret');
        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

        $response = file_get_contents($verifyUrl . '?secret=' . $secretKey . '&response=' . $captchaResponse);
        $responseKeys = json_decode($response, true);
        return $responseKeys;
        return isset($responseKeys["success"]) && $responseKeys["success"] === true;
    }


    public function validateLogin(Request $request)
    {
        try {

            $captchaResponse = $request->input('g-recaptcha-response');

            if (!$this->validateCaptcha($captchaResponse)) {
                return response()->json(['message' => 'Captcha tidak valid.'], 422);
            }

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
