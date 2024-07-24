<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Floor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ["name", "description", "image", "building_id"];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function cctvs()
    {
        return $this->hasMany(Cctv::class);
    }
}
