<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class TelegramHistory extends Model
{
    use HasFactory;
    protected $fillable = ['from_id', 'to_id', 'message_id', 'message'];

    public static function setHistory($messageId, $fromId, $toId, $message) {
        try {
//            TelegramHistory::query()->updateOrCreate([
//                'message_id' => $messageId,
//                'from_id' => $fromId,
//                'message' => substr((string)$message, 0, 50),
//                'to_id' => $toId
//            ]);
        } catch (Exception $e) {
//            Log::debug("Telegram Controller, function start");
//            Log::debug($e->getMessage());
        }
    }

    public function member()
    {
        return $this->hasOne(Member::class, 'telegram_id', 'to_id');
    }

    public function fromMember(){
        return $this->hasOne(Member::class, 'telegram_id', 'from_id');
    }
}
