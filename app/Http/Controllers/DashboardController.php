<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class DashboardController extends Controller
{
    public function index()
    {
        $user = json_decode(Cookie::get("user"));
        $title = $user->username;
        return view("pages.dashboard.index", compact("title"));
    }
}
