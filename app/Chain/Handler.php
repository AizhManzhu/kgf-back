<?php

namespace App\Chain;

use App\Structure\TelegramData;

interface Handler
{

    public function setNext(Handler $handler): Handler;

    public function handle(TelegramData $data);

}
