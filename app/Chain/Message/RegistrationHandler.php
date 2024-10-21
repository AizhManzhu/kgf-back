<?php

namespace App\Chain\Message;

use App\Chain\AbstractMessageHandler;
use App\Structure\TelegramData;
use App\Models\Member;
use App\Models\TelegramRequestLog;
use App\Repository\MemberRepository;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class RegistrationHandler extends AbstractMessageHandler
{
    const REGISTRATION_BY_EMAIL = "reg_email";

    const FOUND_MESSAGE = "Поздравляем, Вы подключили бот Kazakhstan Growth Forum (KGF)!
С помощью этого бота Вы можете просмотреть программу мероприятия, следить за эфиром и наладить полезные знакомства с помощью нетворкинга";
    const NOT_FOUND_MESSAGE = "Ваши данные не найдены.";

    public function handle(TelegramData $data)
    {
        Log::debug("registration");
        if ($data->command == self::REGISTRATION_BY_EMAIL) {
            $memberRepository = new MemberRepository(new Member());
            $telegramRequestLog = TelegramRequestLog::query()->where('telegram_id', $data->telegramId)->first();
            $member = $memberRepository->getByAttributes(['*'], ['email' => $data->message]);
            if (!$member) {
                $message = self::NOT_FOUND_MESSAGE;
            } else {
                $message = self::FOUND_MESSAGE;
                $member->telegram_id = $data->telegramId;
                $member->save();
                $telegramRequestLog->delete();
            }

            return Telegram::sendMessage([
                'chat_id' => $data->telegramId,
                'text' => $message,
            ]);
        } else {
            return parent::handle($data);
        }
    }
}
