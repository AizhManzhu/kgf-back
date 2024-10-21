<?php

namespace App\Chain;

use App\Structure\TelegramData;

class AbstractMessageHandler implements Handler
{
    /**
     * @var Handler
     */
    private $nextHandler;

    public function setNext(Handler $handler): Handler
    {
        $this->nextHandler = $handler;
        // Возврат обработчика отсюда позволит связать обработчики простым
        // способом, вот так:
        // $monkey->setNext($squirrel)->setNext($dog);
        return $handler;
    }

    public function handle(TelegramData $data)
    {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($data);
        }

        return null;
    }
}
