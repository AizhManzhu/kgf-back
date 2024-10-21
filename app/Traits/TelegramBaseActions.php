<?php

namespace App\Traits;

use App\Models\TelegramHistory;
use App\Models\TelegramRequestLog;
use App\Repository\EventRepositoryInterface;
use App\Repository\MemberRepositoryInterface;
use App\Repository\TelegramRepository;
use App\Repository\TelegramRepositoryInterface;
use Illuminate\Http\Request;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;
/**
 *  TODO Make Telegram registration with base fields;
 * Trait TelegramRegistration
 * @package App\Traits
 */

trait TelegramBaseActions
{

    public function getProgram(Request $request, EventRepositoryInterface $eventRepository)
    {
        $event = $eventRepository->getCurrentEvent();
        $replyMarkup = (new TelegramRepository())->getBaseKeyboard();
        if ($event->image != null) {
            Telegram::sendPhoto([
                'chat_id' => $request->telegram_id,
                'photo' => new InputFile(storage_path('app/public/uploads').'/' . $event->image, $event->image),
                'reply_markup' => $replyMarkup,
            ]);
        } else {
            $result = Telegram::sendMessage([
                'chat_id' => $request->telegram_id,
                'text' => "Уппс...\nНет программы мероприятия",
                'reply_markup' => $replyMarkup,
            ]);
            TelegramHistory::setHistory($result->message_id, $result->from->id, $result->chat->id, $result->text);
        }
    }

    public function getSpeaker(Request $request, EventRepositoryInterface $eventRepository, TelegramRepositoryInterface $telegramRepository)
    {
        $replyMarkup = $telegramRepository->getBaseKeyboard(null);
        $event = $eventRepository->getCurrentSpeaker();
        if (count($event->currentSpeaker) != 0) {
            Telegram::sendPhoto([
                'chat_id' => $request->telegram_id,
                'photo' => new InputFile(storage_path('app/public/uploads').'/' . $event->currentSpeaker[0]->image_path, $event->currentSpeaker[0]->image_path),
                'reply_markup' => $replyMarkup,
            ]);
            if (strlen($event->currentSpeaker[0]->description)>0)
            {
                $result = Telegram::sendMessage([
                    'chat_id' => $request->telegram_id,
                    'text' => $event->currentSpeaker[0]->description,
                    'reply_markup' => $replyMarkup,
                ]);

                TelegramHistory::setHistory($result->message_id, $result->from->id, $result->chat->id, $result->text);
            }
        } else {
            $result = Telegram::sendMessage([
                'chat_id' => $request->telegram_id,
                'text' => "Уппс...\nНичего не найдено",
                'reply_markup' => $replyMarkup,
            ]);
            TelegramHistory::setHistory($result->message_id, $result->from->id, $result->chat->id, $result->text);
        }
    }

    public function getMember(Request $request, MemberRepositoryInterface $memberRepository)
    {
        $members = $memberRepository->getByName($request->message);
        foreach ($members as $member) {
            $replyMarkup = null;
            if (!empty($member->telegram_id) && $request->telegram_id != $member->telegram_id) {
                $replyMarkup = Keyboard::make([
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                    'inline_keyboard' => [
                        [
                            ['text' => 'Написать', 'callback_data' => $this->generateNetworkCallback($member->telegram_id, self::NETWORKING)],
                        ]
                    ]]);
            }
            $message = "<b>ФИО</b>: $member->first_name $member->last_name \n<b>Компания</b>: $member->company \n<b>Должность</b>: $member->position";
            $toResult = Telegram::sendMessage([
                'chat_id' => $request->telegram_id,
                'parse_mode' => 'html',
                'text' => $message,
                'reply_markup' => $replyMarkup
            ]);
            if (isset($member->telegram_id)) {
                TelegramRequestLog::query()->where('telegram_id', $member->telegram_id)
                    ->updateOrCreate(
                        ['telegram_id' => $member->telegram_id],
                        ['telegram_id' => $member->telegram_id, 'message_id' => $toResult->message_id]
                    );
            }
            TelegramHistory::setHistory($toResult->message_id, $toResult->from->id, $toResult->chat->id, $toResult->text);
        }
    }
}
