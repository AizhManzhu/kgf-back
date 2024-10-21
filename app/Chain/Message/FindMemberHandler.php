<?php

namespace App\Chain\Message;

use App\Chain\AbstractMessageHandler;
use App\Models\Member;
use App\Models\TelegramRequestLog;
use App\Repository\MemberRepository;
use App\Repository\TelegramRepository;
use App\Services\ButtonMessage\FindMemberService;
use App\Structure\TelegramData;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class FindMemberHandler extends AbstractMessageHandler
{

    const NETWORKING = 'n',
        NETWORKING_ACCEPT = 'na',
        NETWORKING_DECLINE = 'nd',
        NETWORKING_MESSAGE = 'nm';
    const NOT_FOUND = "Участник не найден:( \nДля повторного поиска нажмите на кнопку 'Найти участник'";

    private TelegramRepository $telegramRepository;

    public function __construct()
    {
        $this->telegramRepository = new TelegramRepository();
    }

    public function handle(TelegramData $data)
    {
        if ($data->command == FindMemberService::COMMAND)
        {
            $memberRepository = new MemberRepository(new Member());

            $members = $memberRepository->getByName($data->message);

            if (count($members)===0)
            {
                Telegram::sendMessage([
                    'chat_id' => $data->telegramId,
                    'text' => self::NOT_FOUND,
                    'reply_markup' => $this->telegramRepository->getBaseKeyboard()
                ]);
                TelegramRequestLog::query()->updateOrCreate(
                    ['telegram_id' => $data->telegramId],
                    ['telegram_id' => $data->telegramId, 'command' => null]
                );
                return null;
            }

            foreach ($members as $member) {
                $replyMarkup = null;
                if (!empty($member->telegram_id) && $data->telegramId != $member->telegram_id) {
                    $replyMarkup = Keyboard::make([
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true,
//                        'inline_keyboard' => [
//                            [
//                                ['text' => 'Написать', 'callback_data' => $this->generateNetworkCallback($member->telegram_id, self::NETWORKING)],
//                            ]
                        ]);
                }
                $message = "*ФИО:* $member->first_name $member->last_name \n*Компания:* $member->company \n*Должность:* $member->position";
                Telegram::sendMessage([
                    'chat_id' => $data->telegramId,
                    'parse_mode' => 'MarkdownV2',
                    'text' => $message,
                    'reply_markup' => $replyMarkup
                ]);
                TelegramRequestLog::query()->updateOrCreate(
                        ['telegram_id' => $data->telegramId],
                        ['telegram_id' => $data->telegramId, 'command' => null]
                    );
            }
            Telegram::sendMessage([
                'chat_id' => $data->telegramId,
                'text' => "Для повторного поиска нажмитие кнопку 'Найти участника'",
                'reply_markup' => $this->telegramRepository->getBaseKeyboard()
            ]);
            return null;
        } else {
            return parent::handle($data);
        }
    }

    private function generateNetworkCallback($to, $tag): string
    {
        return "$tag:$to";
    }

    private function checkLength(TelegramData $data)
    {
        if (strlen($data->message) < 3) {
            Telegram::sendMessage([
                'chat_id' => $data->telegramId,
                'text' => self::NOT_FOUND,
                'reply_markup' => $this->telegramRepository->getBaseKeyboard()
            ]);
            return null;
        }

    }
}
