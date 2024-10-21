<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Answer extends Model
{
    use HasFactory;
    protected $fillable = [
        'answer',
        'telegram_id',
        'answer_date_time',
        'round'
    ];

    public function member(): HasOne
    {
        return $this->hasOne(Member::class, 'telegram_id', 'telegram_id');
    }
}
