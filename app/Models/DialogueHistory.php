<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DialogueHistory extends Model
{
    use HasFactory;

    protected $fillable = ['telegram_id', 'name', 'message_id'];
}
