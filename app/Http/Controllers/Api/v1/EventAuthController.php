<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventMember;
use App\Repository\EventRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EventAuthController extends Controller
{
    public function check(Request $request)
    {
        $id = ((new EventRepository(new Event()))->getCurrentEvent())->id;

        $search = $request->get('search');
        $result = DB::select(DB::raw(" select event_members.id as event_member_id, members.id as id, members.first_name,
                            members.last_name, members.phone, members.telegram_id, event_members.here, event_members.paid
                        from event_members
                            left join members on members.id = event_members.member_id
                            left join promocodes on promocodes.id = event_members.promocode_id
                        where (event_members.id like '%".$search."%'
                            or members.first_name like '%".$search."%' or members.last_name like '%".$search."%')
                            and event_members.event_id = $id and event_members.paid = 1"));
        return $result;
    }

    public function set(Request $request)
    {
        $eventMember = EventMember::query()->find($request->event_member_id);
        $eventMember->here = 1;
        $eventMember->save();
    }

    public function generateQR(Request $request)
    {
        $hash = $request->id . "-" . md5($request->id . env('TELEGRAM_BOT_TOKEN'));
        return QrCode::size(300)->generate("https://t.me/KgfKZBot?start=$hash");
    }
}
