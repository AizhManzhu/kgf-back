<?php

namespace App\Services\ButtonMessage;

use App\Structure\TelegramData;
use App\Models\Event;
use App\Repository\EventRepository;
use App\Repository\TelegramRepository;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Laravel\Facades\Telegram;

class SpeakerService implements ButtonServiceInterface
{
    public function execute(TelegramData $data)
    {
        $eventRepository = new EventRepository(new Event());
        $replyMarkup = (new TelegramRepository())->getBaseKeyboard();
        $event = $eventRepository->getCurrentEvent();

        if (count($event->currentSpeaker) != 0  ) {
            Telegram::sendPhoto([
                'chat_id' => $data->telegramId,
                'photo' => new InputFile(storage_path('app/public/uploads').'/' . $event->currentSpeaker[0]->image_path, $event->currentSpeaker[0]->image_path),
                'reply_markup' => $replyMarkup,
            ]);
            if (strlen($event->currentSpeaker[0]->description) > 0) {
                Telegram::sendMessage([
                    'chat_id' => $data->telegramId,
                    'text' => $event->currentSpeaker[0]->description,
                    'reply_markup' => $replyMarkup,
                ]);
            }
        } else {
            Telegram::sendMessage([
                'chat_id' => $data->telegramId,
                'text' => "Уппс...\nНичего не найдено",
                'reply_markup' => $replyMarkup,
            ]);
        }
    }
}
