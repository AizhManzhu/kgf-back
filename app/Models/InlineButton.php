<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InlineButton extends Model
{
    use HasFactory;

    protected $table = 'inlinebuts';
    protected $fillable = [
        'name',
        'text',
        'row',
        'callback_data',
        'auto_answer_id',
        'event_id'
    ];
}
