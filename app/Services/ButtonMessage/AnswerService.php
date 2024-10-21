<?php

namespace App\Services\ButtonMessage;

use App\Models\TelegramRequestLog;
use App\Structure\TelegramData;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class AnswerService implements ButtonServiceInterface
{
    const COMMAND = 'answer';
    const MESSAGE = 'Выберите раунд:';
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
            'text' => self::MESSAGE,
            'reply_markup' => Keyboard::make([
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
                'keyboard' => [
                    ['1 раунд'] , ['2 раунд']
                ]
            ]),
        ]);
    }
}
