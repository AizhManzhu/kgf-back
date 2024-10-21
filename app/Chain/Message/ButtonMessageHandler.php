<?php

namespace App\Chain\Message;

use App\Chain\AbstractMessageHandler;
use App\Structure\TelegramData;

class ButtonMessageHandler extends AbstractMessageHandler
{
    const BUTTON_MESSAGE_LIST = [
        'Найти участника' => 'FindMemberService',
        'Кто в эфире' => 'SpeakerService',
        'Программа' => 'ProgramService',
        'Ответить' => 'AnswerService',
        '1 раунд' => 'RoundService',
        '2 раунд' => 'RoundService',
    ];
    const BUTTON_LIST = [
        'Кто в эфире',
        'Программа',
        'Найти участника',
        'Ответить',
    ];

    const ROUND_LIST = [
        '1 раунд',
        '2 раунд'
    ];

    const NAMESPACE = 'App\\Services\\ButtonMessage\\';

    public function handle(TelegramData $data)
    {
        if (in_array($data->message, self::BUTTON_LIST) || in_array($data->message, self::ROUND_LIST)) {
            $class = $this->getClass($data->message);
            return (new $class())->execute($data);
        } else {
            return parent::handle($data);
        }
    }

    private function getClass(string $message): string
    {
        return self::NAMESPACE.self::BUTTON_MESSAGE_LIST[$message];
    }
}
