<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['logo_header', 'topbar_color', 'sidebar_color', 'bg_color', 'web_title', 'web_logo', 'web_description'];
}
