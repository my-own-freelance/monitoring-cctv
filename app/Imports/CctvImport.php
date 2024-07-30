<?php

namespace App\Imports;

use App\Models\Cctv;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CctvImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Cctv([
            'name' => $row['name'], // Nama kolom CSV harus sesuai dengan heading row
            'url' => $row['url'],
            'building_id' => $row['building_id'],
            'floor_id' => $row['floor_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
