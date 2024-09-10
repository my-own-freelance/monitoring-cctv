<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cctv extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ["name", "url", "building_id", "floor_id", "is_active"];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function userCctv()
    {
        return $this->hasMany(UserCctv::class);
    }
}
