<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramRequestLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'telegram_id',
        'message_id',
        'data',
        'command'
    ];
}
