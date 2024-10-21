<?php

namespace App\Services\ButtonMessage;

use App\Models\TelegramRequestLog;
use App\Repository\TelegramRepository;
use App\Structure\TelegramData;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class RoundService implements ButtonServiceInterface
{
    const COMMAND = 'round';
    const MESSAGE = 'Отправьте ответ: ';
    public function execute(TelegramData $data)
    {
        TelegramRequestLog::query()->updateOrCreate([
            'telegram_id' => $data->telegramId
        ],[
            'telegram_id' => $data->telegramId,
            'command' => self::COMMAND.':'.$data->message
        ]);

        Telegram::sendMessage([
            'chat_id' => $data->telegramId,
            'text' => self::MESSAGE,
            'reply_markup' => (new TelegramRepository())->getBaseKeyboard()
        ]);
    }
}
