<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FieldVipValues extends Model
{
    use HasFactory;
    protected $fillable = ['field_id', 'value', 'email', 'event_id'];

    public function member(): HasOne
    {
        return $this->hasOne(Member::class, 'email', 'email');
    }
}
