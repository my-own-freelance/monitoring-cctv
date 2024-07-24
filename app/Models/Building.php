<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Building extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ["name", "description", "image"];

    public function floors()
    {
        return $this->hasMany(Floor::class);
    }

    public function cctvs()
    {
        return $this->hasMany(Cctv::class);
    }

    public function userBuildings()
    {
        return $this->hasMany(UserBuilding::class);
    }
}
