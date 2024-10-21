<?php

namespace App\Services\ButtonMessage;

use App\Structure\TelegramData;
use App\Models\Event;
use App\Repository\EventRepository;
use App\Repository\TelegramRepository;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Laravel\Facades\Telegram;

class ProgramService implements ButtonServiceInterface
{
    public function execute(TelegramData $data)
    {
        $event = (new EventRepository(new Event()))->getCurrentEvent();
        $replyMarkup = (new TelegramRepository())->getBaseKeyboard();
        if ($event->image != null) {
            Telegram::sendPhoto([
                'chat_id' => $data->telegramId,
                'photo' => new InputFile(storage_path('app/public/uploads').'/' . $event->image, $event->image),
                'reply_markup' => $replyMarkup,
            ]);
        } else {
            Telegram::sendMessage([
                'chat_id' => $data->telegramId,
                'text' => "Уппс...\nНет программы мероприятия",
                'reply_markup' => $replyMarkup,
            ]);
        }
    }
}
