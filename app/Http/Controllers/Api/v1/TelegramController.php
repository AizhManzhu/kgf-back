<?php

namespace App\Http\Controllers\Api\v1;

use App\Chain\Message\AnswerHandler;
use App\Chain\Message\ButtonMessageHandler;
use App\Chain\Message\FindMemberHandler;
use App\Chain\Message\NotFoundHandler;
use App\Chain\Message\RegistrationHandler;
use App\Structure\TelegramData;
use App\Http\Controllers\Controller;
use App\Models\TelegramRequestLog;
use App\Repository\TelegramRepositoryInterface;
use Illuminate\Http\Request;

class TelegramController extends Controller
{
    protected TelegramRepositoryInterface $telegramRepository;

    public function __construct()
    {
        $this->middleware('telegram.token');
    }

    public function message(Request $request)
    {
        $registrationHandler = new RegistrationHandler();
        $findMemberHandler = new FindMemberHandler();
        $buttonMessageHandler = new ButtonMessageHandler();
        $answerHandler = new AnswerHandler();
        $notFoundHandler = new NotFoundHandler();

        $chatId = $request->get('telegram_id');
        $message = $request->get('message');
        $telegramRequestLog = TelegramRequestLog::query()->where('telegram_id', $chatId)->first();

        $telegramData = new TelegramData($chatId, $message, $telegramRequestLog->command??"", $request->date??0);

        $registrationHandler
            ->setNext($buttonMessageHandler)
            ->setNext($findMemberHandler)
            ->setNext($answerHandler)
            ->setNext($notFoundHandler);

        $registrationHandler->handle($telegramData);
        return 1;
    }

    public function callback(Request $request)
    {

    }
}
