<?php

namespace App\Traits;

use App\Http\Controllers\Api\TelegramController;
use App\Models\EventMember;
use App\Models\Field;
use App\Models\FieldValues;
use App\Models\Member;
use App\Models\TelegramHistory;
use App\Models\TelegramRequestLog;
use App\Repository\EventRepository;
use App\Repository\EventRepositoryInterface;
use App\Repository\FieldRepository;
use App\Repository\FieldRepositoryInterface;
use App\Repository\MemberRepository;
use App\Repository\MemberRepositoryInterface;
use App\Repository\TelegramRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

/**
 *  TODO Make Telegram registration with base fields;
 * Trait TelegramRegistration
 * @package App\Traits
 */

trait TelegramRegistration
{

    protected $telegramId;
    protected $field;
    protected $event;
    protected $isRegistered = false;

    /**
     * @param $telegramId
     * @param MemberRepositoryInterface $memberRepository
     * @param EventRepositoryInterface $eventRepository
     * @param FieldRepositoryInterface $fieldRepository
     * @param int $isBase
     * @return bool
     */
    public function registration($telegramId,
                                 MemberRepositoryInterface $memberRepository,
                                 EventRepositoryInterface $eventRepository,
                                 FieldRepositoryInterface $fieldRepository,
                                 int $isBase = Field::IS_BASE): bool
    {

//        $telegramResult = Telegram::sendMessage([
//            'chat_id' => $telegramId,
//            'text' => "Регистрация закрыта",
//        ]);
//        TelegramHistory::setHistory($telegramResult->message_id, $telegramResult->from->id, $telegramResult->chat->id, $telegramResult->text);
//        return false;
        $this->field = $this->checkFieldValues($eventRepository, $telegramId, $isBase);
        $this->telegramId = $telegramId;
        $this->event = ($eventRepository->getCurrentEvent());

        //TODO REFACTOR IT
        $count = FieldValues::query()
            ->whereTelegramId($this->telegramId)
            ->where('event_id', $this->event->id)
            ->count();
        $baseFieldValueCount = FieldValues::query()->whereTelegramId($this->telegramId)->where('event_id', null)->count();
        $baseFieldCount = Field::query()
            ->where('is_base', Field::IS_BASE)
            ->count();
        $additionalFieldCount = Field::query()
            ->where('is_base', Field::IS_NOT_BASE)
            ->where('event_id',$this->event->id)
            ->count();

        if ($this->field)
        {
            if ($this->field->id == 20) {
                FieldValues::query()->updateOrCreate([
                    'field_id' => $this->field->id,
                    'telegram_id' => $telegramId
                ],[
                    'field_id' => $this->field->id,
                    'value' => "Онлайн",
                    'telegram_id' => $telegramId,
                    'event_id' => $eventRepository->getCurrentEvent()->id
                ]);
                $this->registration($telegramId, $memberRepository, $eventRepository, $fieldRepository, Field::IS_NOT_BASE);
                return false;
            }
            $body = $this->getMessageBody();
            $telegramResult = Telegram::sendMessage($body);
            TelegramHistory::setHistory($telegramResult->message_id, $telegramResult->from->id, $telegramResult->chat->id, $telegramResult->text);
            return false;
        } else {
            if ($count === $additionalFieldCount && $baseFieldCount === $baseFieldValueCount)
            {
                $member = $fieldRepository->createMember($telegramId);
                $eventMember = EventMember::where('event_id', $this->event->id)->where('member_id', $member->id)->first();
                if ($member && (!$eventMember || ($eventMember->is_registered === 1 && $eventMember->is_activated === 0))) {
                    $log = TelegramRequestLog::whereTelegramId($telegramId)->first();
                    if ($log) {
                        $utm = $log->utm;
                    } else {
                        $utm = null;
                    }
                    $eventRepository->addMember(($eventRepository->getCurrentEvent())->id, $member->id, 1, $utm);
                    $eventMember = EventMember::where('event_id', $this->event->id)->where('member_id', $member->id)->first();
                    (new TelegramRepository())->sendActivatedMessage($eventMember->member, $eventMember);
//
//                    Telegram::sendMessage([
//                        'chat_id' => $telegramId,
//                        'text' => $this->event->thank_you_message,
//                    ]);
                    return false;
                } else if ($member && ($eventMember || ($eventMember->is_registered === 1 && $eventMember->is_activated === 1))) {
                    return true;
                }
                else {
                    $telegramResult = Telegram::sendMessage([
                        'chat_id' => $telegramId,
                        'text' => 'Что-то пошло не так:('
                    ]);
                    TelegramHistory::setHistory($telegramResult->message_id, $telegramResult->from->id, $telegramResult->chat->id, $telegramResult->text);
                }
            } else {
                $this->registration($telegramId, $memberRepository, $eventRepository, $fieldRepository, Field::IS_NOT_BASE);
            }
            return false;
        }
    }

    public function checkFieldValues(EventRepositoryInterface $eventRepository, $telegramId, int $isBase = Field::IS_BASE)
    {
        $fieldRepository = (new FieldRepository(new Field()));
        return $fieldRepository->getValues($telegramId, $eventRepository, $isBase);
    }

    protected function getMessageBody()
    {
        $body = [
            'chat_id' => $this->telegramId,
            'text' => $this->field->text
        ];
        if ($this->field->type === 'button' && isset($this->field->buttons)) {
            $inlineButton = [];
            foreach ($this->field->buttons as $button) {
                $inlineButton[] = ['text' => $button->text, 'callback_data' => TelegramController::REGISTRATION.":".$this->field->id.":".$button->id];
            }
            $body['reply_markup'] = Keyboard::make([
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
                'inline_keyboard' => [
                    $inlineButton
                ]]);
            $command = null;
        } else {
            $command = "reg:".$this->field->id;
        }

        TelegramRequestLog::query()
            ->whereTelegramId($this->telegramId)
            ->updateOrCreate([
                'telegram_id' => $this->telegramId,
            ],
                [
                    'telegram_id' => $this->telegramId,
                    'data' => null,
                    'command' => $command
                ]);
        return $body;
    }

    public function registEventField($telegramId, EventRepositoryInterface $eventRepository)
    {

//        $telegramResult = Telegram::sendMessage([
//            'chat_id' => $telegramId,
//            'text' => "Регистрация закрыта",
//        ]);
//        TelegramHistory::setHistory($telegramResult->message_id, $telegramResult->from->id, $telegramResult->chat->id, $telegramResult->text);
        $this->field = $this->checkFieldValues($eventRepository, $telegramId, Field::IS_NOT_BASE);
        Log::debug($this->field);
        $this->telegramId = $telegramId;
        $this->event = ($eventRepository->getCurrentEvent());
        $count = FieldValues::query()
            ->whereTelegramId($this->telegramId)
            ->where('event_id', $this->event->id)
            ->count();
        $additionalFieldCount = Field::query()
            ->where('is_base', Field::IS_NOT_BASE)
            ->where('event_id',$this->event->id)
            ->count();
        if ($count == $additionalFieldCount) {
            $member = (new MemberRepository(new Member()))->getByTelegramId($telegramId);
            $eventRepository->addMember(($eventRepository->getCurrentEvent())->id, $member->id, 1);
            $telegramResult = Telegram::sendMessage([
                'chat_id' => $telegramId,
                'text' => $this->event->thank_you_message,
            ]);

            TelegramHistory::setHistory($telegramResult->message_id, $telegramResult->from->id, $telegramResult->chat->id, $telegramResult->text);
//            Telegram::sendMessage([
//                'chat_id' => $telegramId,
//                'text' => $this->event->description,
//            ]);
            return true;
        } else {
            $memberRepository = new MemberRepository(new Member());
            $fieldRepository = new FieldRepository(new Field());
            if ($this->field->id == 20) {
                FieldValues::query()->updateOrCreate([
                    'field_id' => $this->field->id,
                    'telegram_id' => $telegramId
                ],[
                    'field_id' => $this->field->id,
                    'value' => "Онлайн",
                    'telegram_id' => $telegramId,
                    'event_id' => $eventRepository->getCurrentEvent()->id
                ]);
                $this->registration($telegramId, $memberRepository, $eventRepository, $fieldRepository, Field::IS_NOT_BASE);
                return false;
            }
            $body = $this->getMessageBody();
            $telegramResult = Telegram::sendMessage($body);

            TelegramHistory::setHistory($telegramResult->message_id, $telegramResult->from->id, $telegramResult->chat->id, $telegramResult->text);
            return false;
        }
    }

    public function hasFilledFields($telegramId, $event) {
        $count = FieldValues::where('telegram_id', $telegramId)->where('event_id', $event->id)->count();
        if ($count === 0) {
            $telegramResult = Telegram::sendMessage([
                'chat_id' => $telegramId,
                'text' => $event->welcome_message,
            ]);

            TelegramHistory::setHistory($telegramResult->message_id, $telegramResult->from->id, $telegramResult->chat->id, $telegramResult->text);
        }
    }


    public function checkIsMemberRegistred(MemberRepositoryInterface $memberRepository) {
        $member = $memberRepository->getByTelegramId($this->telegramId);
        if ($member) {
            $this->isRegistered = true;
        }
    }
}
