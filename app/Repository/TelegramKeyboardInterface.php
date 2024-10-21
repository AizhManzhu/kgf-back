<?php
namespace App\Repository;

use Telegram\Bot\Keyboard\Keyboard;

interface TelegramKeyboardInterface
{
    public function getButton($buttons): Keyboard;

}
