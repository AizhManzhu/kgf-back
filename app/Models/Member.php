<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'telegram_id',
        'phone',
        'email',
        'position',
        'company',
        'is_checked',
        'is_active'
    ];

    public array $selects = [
        'id',
        'first_name',
        'last_name',
        'phone',
        'email',
        'position',
        'company',
    ];

    /**
     * Get all of the comments for the Member
     *
     * @return HasManyThrough
     */
    public function eventMembers(): hasManyThrough
    {
        return $this->hasManyThrough(
            Event::class,
            EventMember::class,
            'member_id',
            'id',
            'id',
            'event_id');
    }

    public function fieldValues(): hasMany {
        return $this->hasMany(FieldValues::class, 'telegram_id', 'telegram_id');
    }

    public function fieldVipValues(): hasMany {
        return $this->hasMany(FieldVipValues::class, 'email', 'email');
    }

    public function tasks(): hasMany {
        return $this->hasMany(MemberTask::class);
    }

    public function events(): hasMany {
        return $this->hasMany(EventMember::class, 'member_id', 'id');
    }

    public function telegramRequestLog(): hasOne
    {
        return $this->hasOne(TelegramRequestLog::class, 'telegram_id', 'telegram_id');
    }

    public function memberPromocode(): HasOneThrough
    {
        return $this->hasOneThrough(
            Promocode::class,
            EventMember::class,
            'member_id',
            'id',
            'id',
            'promocode_id'
        );
    }
    public static function boot() {
        parent::boot();

        static::deleting(function($member) {
            $member->events()->delete();
            $member->telegramRequestLog()->delete();
            $member->fieldValues()->delete();
        });
    }
}
