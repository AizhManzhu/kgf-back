<?php

namespace App\Chain\Message;

use App\Chain\AbstractMessageHandler;
use App\Events\RoundAnswerEvent;
use App\Models\Member;
use App\Models\TelegramRequestLog;
use App\Repository\AnswerRepository;
use App\Services\ButtonMessage\RoundService;
use App\Structure\TelegramData;
use Telegram\Bot\Laravel\Facades\Telegram;

class AnswerHandler extends AbstractMessageHandler
{
    const MESSAGE = "Ваш ответ принят.\nДля того что бы ответить на след вопрос нажмите на кнопку 'Ответить'";
    const NOT_AVAILABLE = 'Вы уже ответили';
    public function handle(TelegramData $data)
    {
        $exploded = explode(':',$data->command);
        if ($exploded[0] === RoundService::COMMAND) {
            $answerRepository = new AnswerRepository();
            $isAvailable = $answerRepository->checkForAvailability($exploded[1], $data->telegramId);
            if (!$isAvailable) {
                Telegram::sendMessage([
                    'chat_id' => $data->telegramId,
                    'text' => self::NOT_AVAILABLE,
                ]);
                return 0;
            }
            $answerRepository->create($data);
            TelegramRequestLog::query()->updateOrCreate([
                'telegram_id' => $data->telegramId
            ],[
                'telegram_id' => $data->telegramId,
                'command' => null
            ]);

            $result = 0;
            $round = 1;
            $exploded = explode(':',$data->command);
            $message = str_replace(' ', '', $data->message);
            $message = str_replace('-', '', $message);
            $message = str_replace(',', '', $message);
            $message = str_replace('.', '', $message);
            $message = str_replace('!', '', $message);
            $message = mb_strtolower($message);
            if ($exploded[1] =='1 раунд') {
                if ($message === 'ябогмаркетинга') {
                    $result = 1;
                }
            } else {
                if ($message === 'маркетингэтонемагиямаркетингэтонаукаеюзаниматься') {
                    $result = 1;
                }
                $round = 2;
            }

            $member = Member::query()->where('telegram_id', $data->telegramId)->orderByDesc('id')->first();
            $name = $member->first_name.' '.$member->last_name;
            $dateTime = date('d.m.Y H:i:s', $data->date);

            RoundAnswerEvent::dispatch($data->telegramId, self::MESSAGE, $round, $result, $name, $dateTime);

            return 0;
        } else {
            return parent::handle($data);
        }
    }
}
