<?php


namespace App\Services\ButtonMessage;

use App\Structure\TelegramData;

interface ButtonServiceInterface {

    public function execute(TelegramData $data);

}
