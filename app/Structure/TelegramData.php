<?php

namespace App\Structure;

class TelegramData {

    public string $telegramId;
    public string $message;
    public string $command;
    public int $date;

    public function __construct(...$args)
    {
        $this->telegramId = $args[0];
        $this->message = $args[1];
        $this->command = $args[2];
        $this->date = $args[3];
    }

    public function setTelegramId($telegramId)
    {
        $this->telegramId = $telegramId;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function setCommand($command)
    {
        $this->command = $command;
    }
}
