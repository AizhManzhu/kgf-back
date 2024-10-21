<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Jobs\CommonJob;
use App\Jobs\TelegramMailing;
use App\Models\Event;
use App\Models\EventMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MailingController extends Controller {

    public function mailing(Request $request)
    {
        $attachment = null;
        if ($request->has('file')) {
            $attachment = $request->get('file');
        }
        CommonJob::dispatch($request->get('email'), $request->get('subject'), $request->get('name'), $request->get('message'), $attachment);
    }

    public function messageSending(Request $request)
    {
        if (isset($request->text)) {
            $text = mb_convert_encoding($request->text, 'UTF-8');
        } else {
            $text = null;
        }
        $imageName = null;
        $telegramIds = [];
        if ($request->has('members')){
            $telegramIds = explode(',',$request->get('members'));
        }
        if ($request->hasFile('image')) {
            $imageName = time().'-'.$request->image->getFilename().".".$request->image->getClientOriginalExtension();
            $request->image->move(storage_path("app/public/uploads"), $imageName);
        }
        if ($request->has('filter')) {
            $filter = $request->get('filter');
            [$column, $value] = $this->getFiltered($filter);
            $telegramIds = EventMember::query()->where('event_id', 1)
                ->where($column, $value)
                ->whereHas('member', function($query) {
                    $query->whereNotNull('telegram_id');
                })
                ->with('member')
                ->get()
                ->pluck('member.telegram_id')
                ->toArray();
        }
        if (count($telegramIds)>0) {
            foreach ($telegramIds as $telegramId) {
                TelegramMailing::dispatch($telegramId, $text, $imageName);
            }
        } else {
            Log::debug("Отправлено: ");
            Log::debug($telegramIds);
        }
    }

    private function getFiltered($filter): array
    {
        if ($filter === 'online') {
            return ['format', 0];
        }
        else if ($filter === 'offline') {
            return ['format', 1];
        }
        else if($filter === 'need_mentor') {
            return ['need_mentor', 1];
        }
        else if($filter === 'dont_need_mentor') {
            return ['need_mentor', 0];
        }
        else if($filter === 'sponsor') {
            return ['is_sponsor', 1];
        } else {
            return ['is_sponsor', 0];
        }
    }
}
