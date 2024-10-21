<?php

namespace App\Exports;

use App\Models\EventMember;
use App\Models\TelegramHistory;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\Exportable;



class Report implements FromCollection, ShouldAutoSize, WithMapping, WithHeadings, WithEvents
{
    use Exportable;

    public function collection()
    {
        return TelegramHistory::query()
            ->with('member')
            ->with('fromMember')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Id',
            'От кого',
            'Кому',
            'Сообщение',
        ];
    }

    public function map($telegramHistory): array
    {
        return[
            $telegramHistory->id,
            isset($telegramHistory->fromMember)?$telegramHistory->fromMember->first_name." ".$telegramHistory->fromMember->last_name:"KGF",
            isset($telegramHistory->member)?$telegramHistory->member->first_name." ".$telegramHistory->member->last_name:"KGF",
            $telegramHistory->message,
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

    private function getAnswers($value) {
        if ($value->field_id === self::NEED_MENTOR) {
            $this->mentor = $value->value;
        } elseif ($value->field_id === self::FORMAT) {
            $this->format = $value->value;
        } elseif ($value->field_id === self::IS_PARTNER) {
            $this->partner = $value->value;
        }
    }
}
