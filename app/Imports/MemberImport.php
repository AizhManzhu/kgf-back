<?php

namespace App\Imports;

use App\Models\Event;
use App\Models\EventMember;
use App\Models\Member;
use App\Repository\EventRepository;
use Maatwebsite\Excel\Concerns\ToModel;

class MemberImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $event = (new EventRepository(new Event()))->getCurrentEvent();
        $member = Member::query()
            ->updateOrCreate(
                [
                    'last_name' => $row[1],
                    'phone' => $row[2],
                    'email' => $row[3]
                ],
                [
                    'last_name' => $row[1],
                    'first_name' => $row[0],
                    'phone' => $row[2],
                    'email' => $row[3],
                    'company' => $row[4],
                    'position' => $row[5]
                ]);
        if ($member) {
            EventMember::query()
                ->updateOrCreate([
                    'event_id' => $event->id,
                    'member_id' => $member->id
                ], [
                    'event_id' => $event->id,
                    'member_id' => $member->id,
                    'format' => 1,
                    'is_activated' => 1,
                    'paid' => 1,
                ]);
        }
        return $member;
    }
}
