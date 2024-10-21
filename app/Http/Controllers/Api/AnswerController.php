<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use Illuminate\Http\JsonResponse;

class AnswerController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $result = Answer::get();
            return $this->handleResponse($result);
        } catch (\Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function destroyAll(): JsonResponse
    {
        $answers = Answer::get();
        foreach ($answers as $answer) {
            $answer->delete();
        }
        return $this->handleResponse(1);
    }

    public function getFirstRoundWinners()
    {
        $answers = Answer::query()
            ->where('answer', 'ябогмаркетинга')->with(['member' => function ($query) {
                return $query->get();
            }])
            ->where('round', '1 раунд')
            ->orderBy('answer_date_time', 'asc')
            ->get();
        $response = [];
        foreach ($answers as $answer) {
            if ($answer->member) {
                $res = [
                    'id' => $answer->id,
                    'result' => $answer->answer === 'ябогмаркетинга' ? 1 : 0,
                    'name' => $answer->member ? $answer->member->last_name . ' ' . $answer->member->first_name : "",
                    'round' => 1,
                    'telegramId' => $answer->telegram_id,
                    'dateTime' => date('d.m.Y H:i:s', $answer->answer_date_time)
                ];
                array_push($response, $res);
            }
        }
        return $response;
    }

    public function getSecondRoundWinners()
    {
        $answers = Answer::query()
            ->where('answer', 'маркетингэтонемагияэтонауканадоеюзаниматься')->with(['member' => function ($query) {
                return $query->get();
            }])
            ->where('round', '2 раунд')
            ->orderBy('answer_date_time', 'asc')
            ->get();
        $response = [];
        foreach ($answers as $answer) {
            if ($answer->member) {
                $res = [
                    'id' => $answer->id,
                    'result' => $answer->answer === 'маркетингэтонемагияэтонауканадоеюзаниматься' ? 1 : 0,
                    'name' => $answer->member ? $answer->member->last_name . ' ' . $answer->member->first_name : "",
                    'round' => 1,
                    'telegramId' => $answer->telegram_id,
                    'dateTime' => date('d.m.Y H:i:s', $answer->answer_date_time)
                ];
                array_push($response, $res);
            }
        }
        return $response;
    }

    public function setYoutubeWinner($name)
    {
        return Answer::query()->create(['answer' => $name, 'telegram_id' => '0', 'round' => 'youtube', 'answer_date_time' => 0]);
    }

    public function getYoutubeWinners() {
        $answers = Answer::query()->where('round', '=', 'youtube')->get();
        $response = [];
        foreach ($answers as $answer) {
            $res = [
                'id' => $answer->id,
                'result' => 1,
                'name' => $answer->answer,
                'round' => 1,
                'telegramId' => 0,
                'dateTime'=> date('d.m.Y H:i:s', time())
            ];
            array_push($response, $res);
        }
        return $response;
    }
}
