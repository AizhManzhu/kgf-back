<?php

namespace App\Repository;

use App\Chain\Message\ButtonMessageHandler;
use App\Exports\EventMembersExport;
use App\Http\Constants;
use App\Jobs\ActivateTelegramMailing;
use App\Jobs\VipMemberJob;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Keyboard\Keyboard;

class TelegramRepository implements TelegramRepositoryInterface
{
    use Base;

    const CLOSE_DIALOG_KEYBOARD = 'Завершить диалог';
    const FIND_MEMBER_KEYBOARD = 'Найти участника';

    public function getBaseKeyboard(string $name = null): Keyboard
    {
        $keyboards = ButtonMessageHandler::BUTTON_LIST;

        if ($name != null) {
            if ($name === self::CLOSE_DIALOG_KEYBOARD) {
                unset($keyboards[self::FIND_MEMBER_KEYBOARD]);
                array_push($keyboards, self::CLOSE_DIALOG_KEYBOARD);
            } else {
                unset($keyboards[self::CLOSE_DIALOG_KEYBOARD]);
                array_push($keyboards, self::FIND_MEMBER_KEYBOARD);
            }
        }
        $buttons = $keyboards;

        $keyboard = [];
        foreach ($buttons as $button)
        {
            $keyboard[] = [['text' => $button]];
        }
        return Keyboard::make([
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
            'keyboard' => $keyboard
        ]);
    }

    public function sendActivatedMessage($member, $eventMember)
    {
        //TODO get from template
        $member = $member->load('fieldValues');
        $memberData = json_decode($eventMember->member_data);
        $telegramMessage = Constants::getAcceptOffline($member->last_name." ".$member->first_name, false);
        $emailMessage = Constants::getAcceptOffline($member->last_name." ".$member->first_name);
        $res = null;
        if (isset($memberData->changed_fields)) {
            $res = collect($memberData->changed_fields)->toArray();
        }
        if (isset($memberData->changed_fields) && isset($res[EventMembersExport::FORMAT]) && $res[EventMembersExport::FORMAT]->new_value == "Онлайн") {
            $telegramMessage = Constants::getMessageAfterChangeFormat($member->last_name." ".$member->first_name, false);
            $emailMessage = Constants::getMessageAfterChangeFormat($member->last_name." ".$member->first_name);
        } else {
            if (!empty($member->fieldValues) && isset($member->fieldValues) && count($member->fieldValues) > 0) {
                foreach ($member->fieldValues as $fieldValue) {
                    if ($fieldValue->field_id == EventMembersExport::FORMAT) {
                        if ($fieldValue->value == Constants::ONLINE) {
                            $telegramMessage = Constants::getAcceptOnline($member->last_name." ".$member->first_name, false);
                            $emailMessage = Constants::getAcceptOnline($member->last_name." ".$member->first_name);
                        }
                    }
                }
            } else {
                foreach ($member->fieldVipValues as $fieldValue) {
                    if ($fieldValue->field_id == EventMembersExport::FORMAT) {
                        if ($fieldValue->value == Constants::ONLINE) {
                            $telegramMessage = Constants::getAcceptOnline($member->last_name." ".$member->first_name, false);
                            $emailMessage = Constants::getAcceptOnline($member->last_name." ".$member->first_name);
                        }
                    }
                }
            }
        }
        Log::debug("Telegram Repository telegram_id: ");
        Log::debug($member->telegram_id);
        if (!empty($member->telegram_id)) {
            ActivateTelegramMailing::dispatch($member->telegram_id, $telegramMessage, $this->getBaseKeyboard(), $eventMember->id);
        }
        if (!empty($member->email)) {
            VipMemberJob::dispatch($member->id, $member->first_name.' '.$member->last_name, $member->email, $emailMessage, $eventMember->id);
        }
    }

    public function replaceDiv($text): string
    {
        $pos = strpos($text, "<div>");
        if ($pos !== false) {
            $text = substr_replace($text, chr(10), $pos, strlen("<div>"));
        }
        $text = str_replace('</p>',PHP_EOL, $text);
        $text = str_replace('</div>',PHP_EOL, $text);
        $text = str_replace('</p>',PHP_EOL, $text);
        $text = str_replace('<p>','', $text);
        $text = strip_tags($text, ['<b>', '<strong>', '<i>', '<em>', '<a>', '<code>', '<pre>', '<u>']);
        $nbsp = html_entity_decode("&nbsp;");
        $text = html_entity_decode($text);
        str_replace($nbsp,chr(32), $text);

        return $text;
    }
}
