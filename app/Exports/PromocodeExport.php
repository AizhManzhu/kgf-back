<?php

namespace App\Exports;

use App\Models\Member;
use App\Models\Promocode;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\Exportable;



class PromocodeExport implements FromCollection, ShouldAutoSize, WithHeadings, WithEvents
{

    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Promocode::select('id', 'code', 'max_try', 'is_activated', 'used', 'created_at', 'updated_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Id',
            'Promocode',
            'Max Try',
            'Is Activated',
            'Is Used',
            'Created At',
            'Updated At',
        ];
    }

    public function map($promocode): array{
        if ($promocode->id == 129) {
            Log::debug($promocode->code);
        }
        return [
            $promocode->id,
            "'$promocode->code'",
            $promocode->max_try,
            $promocode->is_activated,
            $promocode->used,
            $promocode->created_at,
            $promocode->updated_at
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $cellRange = 'A1:W1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(14);
            },
        ];
    }
}
