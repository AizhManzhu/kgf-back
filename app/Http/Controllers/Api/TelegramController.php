<?php

namespace App\Http\Controllers\Api;

use App\Models\Competition;
use App\Models\MemberTask;
use App\Models\BaseKeyboard;
use App\Models\Button;
use App\Models\Event;
use App\Models\EventMember;
use App\Models\Field;
use App\Models\FieldValues;
use App\Models\FieldVipValues;
use App\Models\Member;
use App\Models\TelegramHistory;
use App\Models\TelegramRequestLog;
use App\Models\DialogueHistory;
use App\Repository\EventRepository;
use App\Repository\EventRepositoryInterface;
use App\Repository\FieldRepository;
use App\Repository\MemberRepository;
use App\Repository\MemberRepositoryInterface;
use App\Repository\TelegramRepository;
use App\Repository\TelegramRepositoryInterface;
use App\Repository\DialogueHistoryRepository;
use App\Traits\TelegramBaseActions;
use App\Traits\TelegramRegistration;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    use TelegramRegistration;
    use TelegramBaseActions;

    /** TODO: make thin controller **/

    protected $baseButtonList = [5,6,7];
    protected $telegramRepository;

    const NETWORKING = 'n',
        NETWORKING_ACCEPT = 'na',
        NETWORKING_DECLINE = 'nd',
        NETWORKING_MESSAGE = 'nm';
    const REGISTRATION = 'reg';
    const REGISTRATION_NEW = 'regn';
    const FIELD = 'field';
    const IM_IN = "im_in";
    const ROOM = "room";
    const GO_TO_ROOM = "gotoroom";
    const COMPETITION = "competition";
    const INPUT_USER_FIO = "С помощью функционала «Найти коллегу» можно посмотреть краткую информацию об участнике данного мероприятия и отправить ему запрос с предложением пообщаться с вами для расширения круга интересных и полезных знакомств.

Введите фамилию или имя участника";
    const REGISTRATION_BY_EMAIL = "reg_email";


    public function __construct(TelegramRepositoryInterface $telegramRepository) {
        $this->middleware('telegram.token');
        $this->telegramRepository = $telegramRepository;
    }

    public function start(Request $request, EventRepositoryInterface $eventRepository) {
        $telegramId = $request->telegram_id;
        $event = $eventRepository->getCurrentEvent();
        $replyMarkup = (new TelegramRepository())->getBaseKeyboard();
        $telegramResult = Telegram::sendMessage([
            'chat_id' => $telegramId,
            'text' => $event->description,
            'reply_markup' => $replyMarkup,
        ]);
        TelegramHistory::setHistory($telegramResult->message_id, $telegramResult->from->id, $telegramResult->chat->id, $telegramResult->text);
    }
    /**
     * @deprecated
     * @param Request $request
     * @param EventRepositoryInterface $eventRepository
     * @param MemberRepositoryInterface $memberRepository
     */
    public function deprecatedStart(Request $request, EventRepositoryInterface $eventRepository, MemberRepositoryInterface $memberRepository)
    {
        $telegramId = $request->telegram_id;
        TelegramHistory::setHistory(0, $telegramId, 0, '/start');
        $event = $eventRepository->getCurrentEvent();
        if(!empty($request->vip_member_id) && !empty($request->vip_hash)){
            $vipMember =  Member::find($request->vip_member_id);
            if ($vipMember) {
                $hash = $vipMember->id . md5($vipMember->id . env('TELEGRAM_BOT_TOKEN'));
                if ($hash === $request->vip_hash) {
                    $vipMember->telegram_id = $telegramId;
                    $vipMember->save();

                    $fieldVipValues = FieldVipValues::query()
                        ->where('email', $vipMember->email)->get();

                    $hasFieldValue = FieldValues::query()->where('telegram_id', $telegramId)->get();

                    if ($hasFieldValue) {
                        foreach ($hasFieldValue as $value) {
                            $value->delete();
                        }
                    }

                    foreach ($fieldVipValues as $value) {
                        $fieldValues = new FieldValues();
                        $fieldValues->field_id = $value->field_id;
                        $fieldValues->value = $value->value;
                        $fieldValues->telegram_id = $telegramId;
                        $fieldValues->event_id = $value->event_id;
                        $fieldValues->save();

                        $value->delete();
                    }

                    $vipMemberLog = TelegramRequestLog::where('telegram_id', $telegramId)->first();

                    if (!$vipMemberLog) {
                        $vipMemberLog = new TelegramRequestLog();
                        $vipMemberLog->telegram_id = $telegramId;
                        $vipMemberLog->save();
                    }

                    $result = Telegram::sendMessage([
                        'chat_id' => $telegramId,
                        'parse_mode' => 'html',
                        'text' => "Вы подключили бот Kazakhstan Growth Forum (KGF)!\n" . $event->thank_you_message,
                    ]);
                    TelegramHistory::setHistory($result->message_id, $result->from->id, $result->chat->id, $result->text);
                }
                return;
            }
        }

        $member = $memberRepository->getByTelegramId($telegramId, true);
        if (!$member) {
            $replyMarkup = Keyboard::make([
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
                'inline_keyboard' => [
                    [
                        ['text' => 'Да', 'callback_data' => TelegramController::REGISTRATION_NEW . ":ol_yes"],
                        ['text' => 'Нет', 'callback_data' => TelegramController::REGISTRATION_NEW . ":new_no"],
                    ]
                ]]);
            $telegramResult = Telegram::sendMessage([
                'chat_id' => $telegramId,
                'text' => 'Вы регистрировались?',
                'reply_markup' => $replyMarkup
            ]);
            TelegramHistory::setHistory($telegramResult->message_id, $telegramId, $telegramResult->from->id, $telegramResult->text);
            return;
//            $this->hasFilledFields($telegramId, $event);
//            $fieldRepository = (new FieldRepository(new Field()));
//            $this->registration($telegramId, $memberRepository, $eventRepository, $fieldRepository);
//            return;
        } else {
            $count = FieldValues::query()
                ->where('telegram_id', $telegramId)
                ->whereIn('field_id', [19, 20, 21])
                ->count();
            $additionalFieldCount = Field::query()
                ->where('is_base', Field::IS_NOT_BASE)
                ->where('event_id',$event->id)
                ->count();
            $vipCount = FieldVipValues::query()
                ->where('email', $member->email)
                ->whereIn('field_id', [19, 20, 21])
                ->count();
            if ($count != $additionalFieldCount && $vipCount != $additionalFieldCount) {
                Log::debug('130');
                $telegramResult = Telegram::sendMessage([
                    'chat_id' => $telegramId,
                    'text' => 'Для участия в Kazakhstan Growth Forum, который состоится 22 сентября 2022 года, ответьте на несколько вопросов'
                ]);
                TelegramHistory::setHistory($telegramResult->message_id, $telegramResult->from->id, $telegramResult->chat->id, $telegramResult->text);
                $result = $this->registEventField($telegramId, $eventRepository);
                if (!$result) {
                    return;
                }
            }
        }
        if ($request->utm) {
            TelegramRequestLog::query()->updateOrCreate(['telegram_id' => $telegramId], ['telegram_id' => $telegramId, 'data' => $request->utm]);
        }
        $replyMarkup = (new TelegramRepository())->getBaseKeyboard();
        $telegramResult = Telegram::sendMessage([
            'chat_id' => $telegramId,
            'text' => $event->description,
            'reply_markup' => $replyMarkup,
        ]);
        TelegramHistory::setHistory($telegramResult->message_id, $telegramResult->from->id, $telegramResult->chat->id, $telegramResult->text);
    }

    public function callbackAction(Request $request, MemberRepositoryInterface $memberRepository, DialogueHistoryRepository $dialogHistory)
    {
        $callback = explode(':', $request->callback);
        $member = $memberRepository->getByTelegramId($request->telegram_id);
        if ($callback[0] === self::NETWORKING) {
            $toMember = $memberRepository->getByTelegramId($callback[1]);
            $this->networking($member, $toMember);
        } elseif ($callback[0] === self::NETWORKING_ACCEPT) {
            $toMember = $memberRepository->getByTelegramId($callback[1]);
            $messageId = $request->message_id;
            $this->closeDialog($member);
            $this->sendNetworkingAccept($member, $toMember);
            $dialogHistory->deleteDialogueHistoryByTelegramId($member, $messageId);
        } elseif ($callback[0] === self::NETWORKING_DECLINE) {
            $toMember = $memberRepository->getByTelegramId($callback[1]);
            $messageId = $request->message_id;
            $this->declineDialog($member, $toMember);
            $this->deleteButton($member, $toMember, $request->message_id);
            $dialogHistory->deleteDialogueHistoryByTelegramId($member, $messageId);
        } elseif($callback[0] === self::COMPETITION){
             TelegramRequestLog::query()->updateOrCreate(['telegram_id' => $request->telegram_id], ['telegram_id' => $request->telegram_id, 'data' => json_encode(['competitionId' => $callback[1], 'taskId' => $callback[2]]), 'command' => "task"]);
             $tasksOfMember = MemberTask::where('member_id',$member->id)->where('task_id', $callback[2])->first();
             if(!$tasksOfMember){
                MemberTask::create([
                  'member_id' => $member->id,
                  'competition_id' => $callback[1],
                  'task_id' => $callback[2],
                  'log_answer' => 'No',
                  'attempt' => 3,
                ]);
             }
             Telegram::sendMessage([
                'chat_id' => $request->telegram_id,
                'text' => "Введите ответ:"
            ]);
        } elseif ($callback[0] === self::REGISTRATION) {
            //todo callback registration
            $fieldId = $callback[1];
            $button = Button::find($callback[2]);
            $eventRepository = (new EventRepository(new Event()));
            FieldValues::query()->updateOrCreate([
                'field_id' => $fieldId,
                'telegram_id' => $request->telegram_id
            ],[
                'field_id' => $fieldId,
                'value' => $button->text,
                'telegram_id' => $request->telegram_id,
                'event_id' => $eventRepository->getCurrentEvent()->id
            ]);
            if(!$member) {
                $result = $this->registration($request->telegram_id, $memberRepository, $eventRepository, (new FieldRepository(new Field())));
                if (!$result) {
                    return;
                }
            } else {
                $eventMember = EventMember::query()->where('is_activated', 1)->where('event_id', $eventRepository->getCurrentEvent()->id)->where('member_id', $member->id)->first();
                if(empty($eventMember)){
                    $result = $this->registEventField($request->telegram_id, $eventRepository);
                    if (!$result) {
                        return;
                    }
                }
            }
        } elseif ($callback[0] === 'im_in') {
            $telegramResult = Telegram::sendMessage([
                'chat_id' => $request->telegram_id,
                'text' => 'Для участия в Kazakhstan Growth Forum, который состоится 22 сентября 2022 года, ответьте на несколько вопросов'
            ]);
            TelegramHistory::setHistory($telegramResult->message_id, $telegramResult->from->id, $telegramResult->chat->id, $telegramResult->text);
            $eventRepository = (new EventRepository(new Event()));
            $result = $this->registEventField($request->telegram_id, $eventRepository);
            if (!$result) {
                return;
            }
        } elseif ($callback[0] == self::ROOM) {
            $replyMarkup = Keyboard::make([
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
                'inline_keyboard' => [
                    [
                        ['text' => 'Иду', 'callback_data' => TelegramController::GO_TO_ROOM . ":".$callback[1]],
                    ]
                ]]);
            if ($callback[1] == 1) {
                $telegramResult = Telegram::sendPhoto([
                    'chat_id' => $request->telegram_id,
                    'photo' => new InputFile(public_path() . '/1.png', '1.png'),
                    'reply_markup' => $replyMarkup,
                ]);
            } else if($callback[1] == 2) {
                $telegramResult = Telegram::sendPhoto([
                    'chat_id' => $request->telegram_id,
                    'photo' => new InputFile(public_path() . '/2.png', '2.png'),
                    'reply_markup' => $replyMarkup,
                ]);
            } else {
                $telegramResult = Telegram::sendPhoto([
                    'chat_id' => $request->telegram_id,
                    'photo' => new InputFile(public_path() . '/3.png', '3.png'),
                    'reply_markup' => $replyMarkup,
                ]);
            }
            TelegramHistory::setHistory($telegramResult->message_id, $telegramResult->from->id, $telegramResult->chat->id, $telegramResult->text);
        } elseif ($callback[0] == self::GO_TO_ROOM) {
            if ($callback[1] == "1") {
                $room ="new models";
                $text = "Оставайтесь в главном зале";
            } elseif ($callback[1] == "2") {
                $room ="new products";
                $text = "Пройдите в соседний зал и следуйте указателям";
            } else {
                $room = "new initiative";
                $text = "Пройдите в соседний зал и следуйте указателям";
            }
            Telegram::sendMessage([
                'chat_id' => $request->telegram_id,
                'text' => $text,
            ]);
            TelegramHistory::setHistory(self::GO_TO_ROOM, $request->telegram_id, 0, $room);
        } elseif ($callback[0] == self::REGISTRATION_NEW && $callback[1] == 'ol_yes') {
            $telegramResult = Telegram::sendMessage([
                'chat_id' => $request->telegram_id,
                'text' => "Укажите e-mail для того чтобы найти ваши данные"
            ]);
            TelegramRequestLog::query()->updateOrCreate(['telegram_id' => $request->telegram_id], ['telegram_id' => $request->telegram_id, 'command' => 'regn']);
            TelegramHistory::setHistory($telegramResult->message_id, $telegramResult->from->id, $telegramResult->chat->id, $telegramResult->text);
        } elseif ($callback[0] == self::REGISTRATION_NEW && $callback[1] == 'new_no') {
            $eventRepository = (new EventRepository(new Event()));
            $result = $this->registration($request->telegram_id, $memberRepository, $eventRepository, (new FieldRepository(new Field())));
            if (!$result) {
                return;
            }
        }
    }

    public function message(Request $request)
    {
        $memberRepository = (new MemberRepository(new Member()));
        $eventRepository = (new EventRepository(new Event()));

        $telegramId = $request->telegram_id;
        TelegramHistory::setHistory(0, $telegramId, 0, $request->message);
        $telegramRequestLog = TelegramRequestLog::query()
            ->where('telegram_id', $telegramId)
            ->first();
        if ($telegramRequestLog) {
            $command = explode(':', $telegramRequestLog->command);
            if ($command[0] && $command[0] == self::REGISTRATION_BY_EMAIL) {
                $event = $eventRepository->getCurrentEvent();
                $member = Member::query()->where('email', $request->message)->whereHas('eventMembers', function ($query) use ($event) {
                    $query->where('event_id', $event->id);
                })->orderByDesc('id')->first();
                if (!$member) {
                    $result = Telegram::sendMessage([
                        'chat_id' => $telegramId,
                        'text' => "Ваши данные не найдены. ",
                    ]);
                    TelegramHistory::setHistory($result->message_id, $result->from->id, $result->chat->id, $result->text);
                    return 1;
                } else {
                    $telegramResult = Telegram::sendMessage([
                        'chat_id' => $request->telegram_id,
                        'text' => "Поздравляем, Вы подключили бот Kazakhstan Growth Forum (KGF)!
С помощью этого бота Вы можете просмотреть программу мероприятия, следить за эфиром и наладить полезные знакомства с помощью нетворкинга",
                        'reply_markup' => (new TelegramRepository())->getBaseKeyboard()
                    ]);
                    $member->telegram_id = $telegramId;
                    $member->save();

                    if ($telegramRequestLog->delete()) {
                        Log::debug('$telegramRequestLog deleted');
                    }

                    TelegramHistory::setHistory($telegramResult->message_id, $telegramResult->from->id, $telegramResult->chat->id, $telegramResult->text);
                }
            }
//            if ($command[0] && $command[0] == 'reg') {
//                $fieldId = $command[1];
//                $value = $request->message;
//                FieldValues::query()->updateOrCreate([
//                    'field_id' => $fieldId,
//                    'telegram_id' => $request->telegram_id,
//                ],[
//                    'value' => $value
//                ]);
//            }
//            if ($command[0] && $command[0] == 'regn') {
//                $member = Member::query()->where('email', $request->message)->first();
//                $member->telegram_id = $request->telegram_id;
//                $member->save();
//                $telegramResult = Telegram::sendMessage([
//                    'chat_id' => $request->telegram_id,
//                    'text' => "Поздравляем, Вы подключили бот Kazakhstan Growth Forum (KGF)!
//С помощью этого бота Вы можете просмотреть программу мероприятия, следить за эфиром и наладить полезные знакомства с помощью нетворкинга",
//                    'reply_markup' => (new TelegramRepository())->getBaseKeyboard()
//                ]);
//                $telegramRequestLog->delete();
//                TelegramHistory::setHistory($telegramResult->message_id, $telegramResult->from->id, $telegramResult->chat->id, $telegramResult->text);
//            }
        }
        $member = $memberRepository->getByTelegramId($telegramId);
        $event = $eventRepository->getCurrentEvent();
//        $eventMember = EventMember::query()->where('event_id', $eventRepository->getCurrentEvent()->id)->where('member_id', $member->id)->first();
//        if ($eventMember->is_activated === 0) {
//            $telegramResult = Telegram::sendMessage([
//                'chat_id' => $telegramId,
//                'text' => $event->description,
//                'reply_markup' => (new TelegramRepository())->getBaseKeyboard()
//            ]);
//            TelegramHistory::setHistory($telegramResult->message_id, $telegramResult->from->id, $telegramResult->chat->id, $telegramResult->text);
//            return 1;
//        }

        $baseKeyboards = BaseKeyboard::query()->where('name', $request->message)->first();
        if($baseKeyboards){
            if ($baseKeyboards->type === 'ether') {
                try {
                    $this->getSpeaker($request, $eventRepository, (new TelegramRepository()));
                    return 1;
                } catch (Exception $e) {
                    Log::debug($e->getMessage());
                }
            } elseif ($baseKeyboards->type === 'program') {
                $this->getProgram($request, $eventRepository);
                return 1;
            } elseif ($baseKeyboards->type === 'cent') {
                return 1;
            } elseif ($baseKeyboards->type === 'findMember') {
//                $telegramResult = Telegram::sendMessage([
//                    'chat_id' => $telegramId,
//                    'text' => self::INPUT_USER_FIO,
//                    'reply_markup' => (new TelegramRepository())->getBaseKeyboard()
//                ]);
//                TelegramHistory::setHistory($telegramResult->message_id, $telegramResult->from->id, $telegramResult->chat->id, $telegramResult->text);
//                TelegramRequestLog::query()
//                    ->updateOrCreate(['telegram_id' => $telegramId], ['telegram_id' => $telegramId,'command' => 'find_member', 'data' => null]);
                return 1;
            } elseif($baseKeyboards->type === 'competition') {
                $eventMember = EventMember::query()->where('is_activated', 1)->where('event_id', $eventRepository->getCurrentEvent()->id)->where('member_id', $member->id)->first();
                if(!empty($eventMember)){
                    $eventMember->is_participated = 1;
                    $eventMember->save();
                }
                $telegramResult = Telegram::sendMessage([
                    'chat_id' => $telegramId,
                    'text' => 'Ура! Вы участвуете в гонке за главный приз - сертификат в магазин косметики и парфюмерии "Золотое Яблоко". Конкурс начнется в 10:20 и закончится в 13:00. Успейте поучаствовать! Желаем удачи!',
                    'reply_markup' => (new TelegramRepository())->getBaseKeyboard()
                ]);
                return 1;
            }
        }

        // TODO make command handler
        $telegramRequestLog = TelegramRequestLog::query()->where('telegram_id', $telegramId)->first();

        if ($telegramRequestLog) {
            $command = $telegramRequestLog->command;
            if ($command === 'find_member') {
//                $this->getMember($request, $memberRepository);
                return 1;
            } elseif($command === 'task') {
                $competitions = Competition::with('tasks')->get();
                $memberTasks = MemberTask::where('member_id', $member->id)->get();
                    foreach ($competitions as $comKey => $competition){
                        $tasks = $competition->tasks;
                        foreach($tasks as $key => $task){
                            foreach($memberTasks as $memberTask){
                            if($memberTask->task_id == $task->id) {
                                    if($memberTask->log_answer == "Yes"){
                                        if($task->answer == $request->message){
                                            return Telegram::sendMessage([
                                                            'chat_id' => $request->telegram_id,
                                                            'text' => "Вы уже ответили на этот вопрос",
                                                        ]);
                                        }
                                    }else{
                                        if($memberTask->attempt == 0){
                                            TelegramRequestLog::query()
                                                    ->updateOrCreate(['telegram_id' => $request->telegram_id], ['telegram_id' => $telegramId,'command' => null, 'data' => null]);
                                            return Telegram::sendMessage([
                                                'chat_id' => $request->telegram_id,
                                                'text' => 'Все попытки исчерпаны. Нам очень жаль, но Вы выбываете из гонки за приз. Желаем Вам хорошего форума и надеемся что скоро увидимся вновь!',
                                            ]);
                                        }else{
                                            if($task->answer == $request->message){
                                                MemberTask::where('task_id', $task->id)->update(['log_answer' => 'Yes']);
                                                Telegram::sendMessage([
                                                            'chat_id' => $request->telegram_id,
                                                            'text' => $competition->after_corr_answer,
                                                        ]);
                                                $keyOfCompetition = $comKey + 1;
                                                if(isset($competitions[$keyOfCompetition])){
                                                        $task = $competitions[$keyOfCompetition]->tasks->random();
                                                        $text = "\n<b>Этап:</b> ".$competitions[$keyOfCompetition]->stage_name.
                                                                "\n<b>Описание:</b> ".$competitions[$keyOfCompetition]->description.
                                                                "\n\n<b>Вопрос:</b> ".$task->question;
                                                        $replyMarkup = Keyboard::make([
                                                            'resize_keyboard' => true,
                                                            'one_time_keyboard' => true,
                                                            'inline_keyboard' => [
                                                                [
                                                                    ['text' => 'Ответить', 'callback_data' =>TelegramController::COMPETITION.":".$competitions[$keyOfCompetition]->id.":".$task->id]
                                                                ]
                                                            ]]);
                                                        return Telegram::sendMessage([
                                                                'parse_mode' => 'HTML',
                                                                'chat_id' => $request->telegram_id,
                                                                'text' => $text,
                                                                'reply_markup' => $replyMarkup,
                                                            ]);
                                                    }
                                            } else {
                                                MemberTask::where('task_id', $task->id)->update(['attempt' => $memberTask->attempt - 1]);
                                                $attemptCount =  MemberTask::where('task_id', $task->id)->first();
                                                if($attemptCount->attempt == 0){
                                                    TelegramRequestLog::query()
                                                            ->updateOrCreate(['telegram_id' => $request->telegram_id], ['telegram_id' => $request->telegram_id,'command' => null, 'data' => null]);
                                                    return Telegram::sendMessage([
                                                        'chat_id' => $request->telegram_id,
                                                        'text' => 'Все попытки исчерпаны. Нам очень жаль, но Вы выбываете из гонки за приз. Желаем Вам хорошего форума и надеемся что скоро увидимся вновь!',
                                                    ]);
                                                } else {
                                                     $attemptText = $attemptCount->attempt == 1 ? " попытка." : " попытки.";
                                                     return  Telegram::sendMessage([
                                                            'chat_id' => $request->telegram_id,
                                                            'text' => 'Упс, это неправильный ответ, попробуйте еще! Осталось еще '.$attemptCount->attempt.$attemptText,
                                                        ]);
                                                }
                                            }
                                        }
                                    }
                            }else{
                                continue;
                            }
                        }
                    }
                }
            }
        }

        if ($member && $member->telegramRequestLog) {
            if ($request->message == TelegramRepository::CLOSE_DIALOG_KEYBOARD) {
                $this->closeDialog($member);
                return 1;
            }
            if ($member->telegramRequestLog->command == self::NETWORKING_MESSAGE) {
                $data = explode(':', $member->telegramRequestLog->data);
                $toMember = $memberRepository->getByTelegramId($data[1]);
                $telegramResult = Telegram::sendMessage([
                    'chat_id' => $toMember->telegram_id,
                    'parse_mode' => 'html',
                    'text' => "<b>$member->first_name $member->last_name:</b> $request->message",
                    'reply_markup' => (new TelegramRepository())->getBaseKeyboard(TelegramRepository::CLOSE_DIALOG_KEYBOARD)
                ]);
                TelegramHistory::setHistory($telegramResult->message_id, $telegramResult->from->id, $telegramResult->chat->id, $telegramResult->text);
                return 1;
            }
        }
        return 1;
    }

    public function closeDialog($member)
    {
        TelegramHistory::setHistory(0, $member->telegram_id, 0, "Завершить диалог");
        $replyMarkup = $this->telegramRepository->getBaseKeyboard(TelegramRepository::FIND_MEMBER_KEYBOARD);
        $requestLogs = TelegramRequestLog::query()
            ->where(function (Builder $query) use ($member) {
                $query->where('data', 'like', "%:$member->telegram_id%")
                    ->orWhere('telegram_id', $member->telegram_id);
            })
            ->whereCommand(self::NETWORKING_MESSAGE)
            ->get();
        foreach ($requestLogs as $log) {
            if (!$log && !$log->telegram_id) continue;
            $telegramResult = Telegram::sendMessage([
                'chat_id' => $log->telegram_id,
                'parse_mode' => 'html',
                'text' => "Диалог завершён",
                'reply_markup' => $replyMarkup,
            ]);
            TelegramHistory::setHistory($telegramResult->message_id, $telegramResult->from->id, $telegramResult->chat->id, $telegramResult->text);
            $log->delete();
        }
    }

    public function deleteButton($member, $toMember, $messageId)
    {
          Telegram::editMessageText([
                'chat_id' => $member->telegram_id,
                'message_id' => $messageId,
                'text' => "С вами хотел(а) бы поговорить $toMember->first_name $toMember->last_name"
          ]);

          TelegramRequestLog::where('telegram_id', $member->telegram_id)->delete();
          TelegramRequestLog::where('telegram_id', $toMember->telegram_id)->delete();
    }

    public function declineDialog($member, $toMember)
    {
        TelegramHistory::setHistory(0, $member->telegram_id, 0, "Отклонить диалог");
        TelegramRequestLog::where('telegram_id', $toMember->telegram_id)->where('command', $this->generateNetworkCallback($toMember->telegram_id, self::NETWORKING_ACCEPT))->delete();
        $replyMarkup = (new TelegramRepository())->getBaseKeyboard();
        $telegramResult = Telegram::sendMessage([
            'chat_id' => $toMember->telegram_id,
            'parse_mode' => 'html',
            'text' => "$member->first_name $member->last_name отклонил ваш запрос",
            'reply_markup' => $replyMarkup,
        ]);
        TelegramHistory::setHistory($telegramResult->message_id, $telegramResult->from->id, $telegramResult->chat->id, $telegramResult->text);
        $telegramResult = Telegram::sendMessage([
            'chat_id' => $member->telegram_id,
            'parse_mode' => 'html',
            'text' => "Диалог с $toMember->first_name $toMember->last_name был отклонён",
            'reply_markup' => $replyMarkup,
        ]);
        TelegramHistory::setHistory($telegramResult->message_id, $telegramResult->from->id, $telegramResult->chat->id, $telegramResult->text);
    }

    private function generateNetworkCallback($to, $tag): string
    {
        return "$tag:$to";
    }

    public function sendNetworkingAccept($fromMember, $toMember)
    {
        $replyMarkup = $this->telegramRepository->getBaseKeyboard(TelegramRepository::CLOSE_DIALOG_KEYBOARD);
        $log = TelegramRequestLog::query()->where('telegram_id', $fromMember->telegram_id)->firstOrCreate(['telegram_id' => $fromMember->telegram_id]);
        $toLog = TelegramRequestLog::query()->where('telegram_id', $toMember->telegram_id)->firstOrCreate(['telegram_id' => $toMember->telegram_id]);
        $log->command = self::NETWORKING_MESSAGE;
        $log->data = $this->generateNetworkCallback($toMember->telegram_id, self::NETWORKING_MESSAGE);
        $toLog->command = self::NETWORKING_MESSAGE;
        $toLog->data = $this->generateNetworkCallback($fromMember->telegram_id, self::NETWORKING_MESSAGE);
        $log->save();
        $toLog->save();
        if ($log->message_id) {
            Telegram::editMessageText([
                'message_id' => $log->message_id,
                'chat_id' => $log->telegram_id,
                'text' => "Диалог с участником $toMember->first_name $toMember->last_name начался!",
            ]);
            TelegramHistory::setHistory(0, $log->message_id, 0, "Диалог с участником $toMember->first_name $toMember->last_name начался!");
        }
        if ($toLog->message_id) {
            Telegram::editMessageText([
                'text' => "С вами хотел(а) бы поговорить $fromMember->first_name $fromMember->last_name",
                'chat_id' => $toMember->telegram_id,
                'message_id' => $toLog->message_id,
            ]);
            TelegramHistory::setHistory(0, $log->message_id, 0, "С вами хотел(а) бы поговорить $fromMember->first_name $fromMember->last_name");
        }
        $telegramResult = Telegram::sendMessage([
            'chat_id' => $toMember->telegram_id,
            'parse_mode' => 'html',
            'text' => "<b>$fromMember->first_name $fromMember->last_name</b> принял(а) ваш запрос. Введите сообщение которое хотите отправить. Для завершения диалога нажмите на кнопку <i>\"Завершить диалог\"</i>",
            'reply_markup' => $replyMarkup,
        ]);
        TelegramHistory::setHistory($telegramResult->message_id, $telegramResult->from->id, $telegramResult->chat->id, $telegramResult->text);
        $telegramResult = Telegram::sendMessage([
            'chat_id' => $fromMember->telegram_id,
            'parse_mode' => 'html',
            'text' => "Введите сообщение которое хотите отправить. Для завершения диалога нажмите на кнопку <i>\"Завершить диалог\"</i>",
            'reply_markup' => $replyMarkup,
        ]);
        TelegramHistory::setHistory($telegramResult->message_id, $telegramResult->from->id, $telegramResult->chat->id, $telegramResult->text);
    }

    public function networking($fromMember, $toMember) {
        $log = TelegramRequestLog::query()->where('telegram_id', $toMember->telegram_id)->firstOrCreate(['telegram_id' => $toMember->telegram_id]);
        Telegram::editMessageText([
            'chat_id' => $fromMember->telegram_id,
            'parse_mode' => 'html',
            'text' => "Мы отправили запрос на начало общение собеседнику от Вашего имени. Пожалуйста, дождитесь ответа, чтобы начать диалог. При необходимости, вы можете отменить запрос на общение, нажав на кнопку «Завершить диалог»

<b>Важно!</b> Нельзя вести диалог одновременно с несколькими участниками. Для начала нового диалога необходимо завершить текущий диалог, нажав на кнопку «Завершить диалог».",
//            'text' => "Ждем ответа от собеседника $toMember->first_name $toMember->last_name",
            'message_id' => $log->message_id,
        ]);
        $replyMarkup = Keyboard::make([
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
            'inline_keyboard' => [
                [
                    ['text' => 'Принять', 'callback_data' => $this->generateNetworkCallback($fromMember->telegram_id, self::NETWORKING_ACCEPT)],
                    ['text' => 'Отказать', 'callback_data' => $this->generateNetworkCallback($fromMember->telegram_id, self::NETWORKING_DECLINE)]
                ]
            ]]);
        $result = Telegram::sendMessage([
            'chat_id' => $toMember->telegram_id,
            'text' => "С вами хотел(а) бы поговорить $fromMember->first_name $fromMember->last_name",
            'reply_markup' => $replyMarkup
        ]);
        TelegramHistory::setHistory($result->message_id, $result->from->id, $result->chat->id, $result->text);
        TelegramRequestLog::updateOrCreate(['telegram_id' => $toMember->telegram_id], ['telegram_id' => $toMember->telegram_id, 'message_id'=>$result->message_id]);
        DialogueHistory::create([
            'telegram_id' => $toMember->telegram_id,
            'name' => "$fromMember->first_name $fromMember->last_name",
            'message_id' => $result->message_id
        ]);
    }
}
