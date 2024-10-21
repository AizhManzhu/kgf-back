<?php

namespace App\Chain\Message;

use App\Chain\AbstractMessageHandler;
use App\Structure\TelegramData;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class NotFoundHandler extends AbstractMessageHandler
{
    public function handle(TelegramData $data)
    {
        Log::debug("NotFound");
        return Telegram::sendMessage([
            'chat_id' => $data->telegramId,
            'text' => "Упс, я еще не знаю таких комманд:(",
        ]);
    }
}
