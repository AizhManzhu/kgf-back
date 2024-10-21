<?php

namespace App\Exports;

use App\Models\EventMember;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\Exportable;



class EventMembersExport implements FromCollection, ShouldAutoSize, WithMapping, WithHeadings, WithEvents
{
    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    public $eventId;
    public $isParticipated;
    /**
     * @var string
     */
    private $partner = null;
    private $mentor = null;
    private $format = null;
    const NEED_MENTOR = 19;
    const FORMAT = 28;
    const IS_PARTNER = 21;
    public function __construct($eventId, $isParticipated)
    {
        $this->eventId = $eventId;
        $this->isParticipated = $isParticipated;
    }

    public function collection()
    {
        if($this->isParticipated){
            return EventMember::query()->with(array('member' => function($query) {
            $query->with('fieldValues')
                ->with('fieldVipValues');
            }))->where('event_id', $this->eventId)->where('is_participated', 1)->get()->pluck('member');
        }else{
            return EventMember::query()->with(array('member' => function($query) {
                $query->with('fieldValues')
                    ->with('fieldVipValues');
            }))->where('event_id', $this->eventId)->get()->pluck('member');
        }
    }

    public function headings(): array
    {
        return [
            'Id',
            'Имя',
            'Фамилия',
            'Телефон',
            'Компания',
            'Должность',
            'email',
            'Формат участия',
            'UTM',
            'Активирован',
            'Участие в конкурсе',
            'Время регистрации',
            'Промокод',
            'Оплачено',
            'Пришел/пришла'
        ];
    }

    public function map($member): array
    {
        foreach ($member->fieldValues as $value) {
            $this->getAnswers($value);
        }
        if ($this->format===null) {
            foreach ($member->fieldVipValues as $value) {
                $this->getAnswers($value);
            }
        }
        $eventMember = EventMember::query()->where('event_id', $this->eventId)
            ->where('member_id', $member->id)->with('promocode')->first();
        $format = $this->format;
        $this->format = null;
        return[
            $eventMember->id,
            $member->first_name,
            $member->last_name,
            $member->phone,
            $member->company,
            $member->position,
            $member->email,
            $format,
            $eventMember->utm,
            $eventMember->is_activated === 1 ? 'Да' : 'Нет',
            $eventMember->is_participated === 1 ? 'Да' : 'Нет',
            $eventMember->created_at,
            $eventMember->promocode?$eventMember->promocode->code:"",
            $eventMember->paid === 1 ? 'Да' : 'Нет',
            $eventMember->here === 1 ? 'Да' : 'Нет',
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
