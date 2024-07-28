<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserCctv extends Model
{
    use HasFactory;

    protected $fillable = ["user_id", "cctv_id"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cctv()
    {
        return $this->belongsTo(Cctv::class);
    }
}
