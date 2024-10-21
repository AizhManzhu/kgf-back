<?php

namespace App\Exports;

use App\Models\ResultOfCompetition;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\Exportable;

class ResultOfCompetitionExport implements FromCollection, ShouldAutoSize,  WithHeadings, WithEvents
{
    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return ResultOfCompetition::select('FIO', 'phone', 'email', 'stage', 'question','answer', 'time')->orderBy('time')->get();
    }

    public function headings(): array
    {
        return [
            'ФИО',
            'Телефон',
            'email',
            'Этап',
            'Вопрос',
            'Ответ',
            'Время'
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
