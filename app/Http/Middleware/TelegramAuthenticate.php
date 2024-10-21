<?php

namespace App\Http\Middleware;

use App\Models\Event;
use App\Models\EventMember;
use App\Models\Member;
use App\Models\TelegramHistory;
use App\Models\TelegramRequestLog;
use App\Repository\EventRepository;
use App\Repository\MemberRepository;
use Closure;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramAuthenticate
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function handle($request, Closure $next)
    {
        $telegramId = $request->telegram_id;
        $event = (new EventRepository(new Event()))->getCurrentEvent();

        $member = EventMember::query()->where('event_id', $event->id)->whereHas('member', function ($query) use ($telegramId) {
            $query->where('telegram_id', $telegramId);
        })->orderByDesc('id')->first();
        if (!$member) {
            if ($request->vip_hash) {
                Log::debug($request->all());
//                [$memberId, $requestHash] = explode('-', $request->utm);
                $member =  Member::find($request->vip_member_id);
                $hash = md5($member->id . env('TELEGRAM_BOT_TOKEN'));
//                if ($hash === $request->vip_hash) {
                $member->telegram_id = $request->telegram_id;
                $member->save();
                TelegramRequestLog::where('telegram_id', $telegramId)->delete();
//                }
                return $next($request);
            }
            Telegram::sendMessage([
                'chat_id' => $telegramId,
                'parse_mode' => 'html',
                'text' => "Для авторизации введите почту",
            ]);
            TelegramRequestLog::query()
                ->updateOrCreate(['telegram_id' => $telegramId], ['telegram_id' => $telegramId,'command' => 'reg_email', 'data' => null]);
            return 1;
        }
        return $next($request);
    }
}
