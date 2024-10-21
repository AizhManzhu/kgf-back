<?php

namespace App\Repository;


use App\Models\Answer;
use App\Structure\TelegramData;

class AnswerRepository
{
    public Answer $model;

    public function __construct()
    {
        $this->model = new Answer();
    }

    public function create(TelegramData $data)
    {
        $exploded = explode(':',$data->command);
        $message = str_replace(' ', '', $data->message);
        $message = str_replace('-', '', $message);
        $message = str_replace(',', '', $message);
        $message = str_replace('.', '', $message);
        $message = mb_strtolower($message);
        return $this->model->create([
            'answer' => $message,
            'telegram_id' => $data->telegramId,
            'answer_date_time' => $data->date,
            'round' => $exploded[1]
        ]);
    }

    public function checkForAvailability($round, $telegramId):int
    {
        $answer = $this->model->where('round', $round)
            ->where('telegram_id', $telegramId)->first();
        return $answer?0:1;
    }
}
