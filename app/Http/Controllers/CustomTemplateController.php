<?php

namespace App\Http\Controllers;

use App\Http\Services\CustomTemplateService;
use App\Models\CustomTemplate;
use Illuminate\Http\Request;

class CustomTemplateController extends Controller
{

    protected $customTemplateService;

    public function __construct(CustomTemplateService $customTemplateService)
    {
        $this->customTemplateService = $customTemplateService;
    }


    public function index()
    {
        $title = "Setting Website";
        return view('pages.admin.setting', compact('title'));
    }

    public function detail()
    {
        return $this->customTemplateService->detail();
    }

    public function saveUpdateData(Request $request)
    {
        return $this->customTemplateService->saveUpdateData($request);
    }
}
