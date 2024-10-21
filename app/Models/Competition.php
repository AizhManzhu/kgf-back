<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competition extends Model
{
    use HasFactory;
    protected $fillable = [
        'stage_name',
        'description',
        'after_corr_answer',
        'event_id'
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
