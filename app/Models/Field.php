<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Field extends Model
{
    use HasFactory, SoftDeletes;

    const IS_BASE = 1;
    const IS_NOT_BASE = 0;

    protected $fillable = ['text', 'type', 'is_base', 'name', 'event_id', 'member_field'];

    public function values(): hasMany
    {
        return $this->hasMany(FieldValues::class);
    }

    public function buttons(): hasMany
    {
        return $this->hasMany(Button::class);
    }

    public function members(): HasManyThrough
    {
        return $this->hasManyThrough(
            Member::class,
            FieldValues::class,
            'field_id',
            'telegram_id',
            'id',
            'telegram_id'
        );
    }

    public function vipMembers(): HasManyThrough
    {
        return $this->hasManyThrough(
            Member::class,
            FieldVipValues::class,
            'field_id',
            'email',
            'id',
            'email'
        );
    }
}
