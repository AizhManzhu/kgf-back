<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberTask extends Model
{
    use HasFactory;
    protected $dateFormat = 'Y-m-d H:i:s.u';
    protected $fillable = [
        'member_id',
        'competition_id',
        'task_id',
        'log_answer',
        'attempt',
    ];

    
}
