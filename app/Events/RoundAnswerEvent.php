<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class RoundAnswerEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $telegramId;
    public string $message;
    public int $round;
    public int $result;
    public string $name;
    public string $dateTime;

    public function __construct(string $telegramId, string $message, int $round, int $result, string $name, string $dateTime)
    {
        $this->telegramId = $telegramId;
        $this->message = $message;
        $this->round = $round;
        $this->result = $result;
        $this->dateTime = $dateTime;
        $this->name = $name;
    }

    public function broadcastOn(): Channel|array
    {
        try {
            Telegram::sendMessage([
                'chat_id' => $this->telegramId,
                'text' => $this->message,
            ]);
            Log::debug('notification-'.$this->round);
            return new Channel('notification-'.$this->round);
        } catch (\Exception $e) {
            Log::debug($e->getTraceAsString());
            return new Channel('error-notification');
        }
    }
}
