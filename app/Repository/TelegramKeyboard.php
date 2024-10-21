<?php

namespace App\Repository;

use Telegram\Bot\Keyboard\Keyboard;

class TelegramKeyboard implements TelegramKeyboardInterface
{

    const BASE_KEYBOARD = [
        [
            'text' => 'Кто в эфире',
        ],
        [
            'text' => 'Программа',
        ],
        [
            'text' => 'cent.kz',
        ],
        [
            'text' => 'Участвую в конкурсе Botan',
        ],
    ];

    public function getButton($buttons): Keyboard
    {
        return Keyboard::button();
    }

    public function getBaseKeyboard(string $keyboard = null): Keyboard
    {
        if ($keyboard != null)
        {
            $buttons = array_merge(self::BASE_KEYBOARD, ['text' => $keyboard]);
        } else {
            $buttons = self::BASE_KEYBOARD;
        }
        return Keyboard::make([
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
            'keyboard' => $buttons
        ]);
    }
}
