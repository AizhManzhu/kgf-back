<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultOfCompetition extends Model
{
    
    use HasFactory;

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = [
        'FIO',
        'question',
        'phone',
        'email',
        'stage',
        'question',
        'answer',
        'time'
    ];
}
