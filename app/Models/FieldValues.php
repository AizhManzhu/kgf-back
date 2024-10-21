<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FieldValues extends Model
{
    use HasFactory;

    protected $fillable = ['field_id', 'value', 'telegram_id', 'event_id'];

    public function field(): HasOne
    {
        return $this->hasOne(Field::class, 'id', 'field_id');
    }
}
