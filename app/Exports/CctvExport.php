<?php

namespace App\Exports;

use App\Models\Cctv;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CctvExport implements FromCollection, WithHeadings
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Cctv::with(["floor" => function ($query) {
            $query->select("id", "name");
        }])->with(["building" => function ($query) {
            $query->select("id", "name");
        }]);


        if ($this->filters['building_id']) {
            $query->where('building_id', $this->filters['building_id']);
        }

        if ($this->filters['floor_id']) {
            $query->where('floor_id', $this->filters['floor_id']);
        }

        return $query->get([
            'id', 'name', 'url', 'created_at', 'updated_at',
            'building_id', 'floor_id'
        ])->map(function ($item, $key) {
            return [
                $key + 1,
                $item->id,
                $item->name,
                $item->url,
                $item->building->name,
                $item->floor->name,
                $item->building->id,
                $item->floor->id,
                $item->created_at,
                $item->updated_at,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'NO',
            'ID',
            'Name',
            'URL',
            'Building Name',
            'Floor Name',
            'Building ID',
            'Floor ID',
            'Created At',
            'Updated At',
        ];
    }
}
