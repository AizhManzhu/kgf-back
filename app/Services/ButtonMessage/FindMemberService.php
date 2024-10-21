<?php

namespace App\Services\ButtonMessage;

use App\Models\TelegramRequestLog;
use App\Structure\TelegramData;
use Telegram\Bot\Laravel\Facades\Telegram;

class FindMemberService implements ButtonServiceInterface
{
    const COMMAND = 'find_member';
    const INPUT_USER_FIO = "С помощью функционала «Найти коллегу» можно посмотреть краткую информацию об участнике данного мероприятия и отправить ему запрос с предложением пообщаться с вами для расширения круга интересных и полезных знакомств.

Введите фамилию или имя участника";

    public function execute(TelegramData $data)
    {
        TelegramRequestLog::query()->updateOrCreate([
            'telegram_id' => $data->telegramId
        ],[
            'telegram_id' => $data->telegramId,
            'command' => self::COMMAND
        ]);

        Telegram::sendMessage([
            'chat_id' => $data->telegramId,
            'text' => self::INPUT_USER_FIO
        ]);
    }
}
