<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Event extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'address', 'event_date', 'short_name', 'is_current',
        'welcome_message', 'thank_you_message', 'price', 'need_pay', 'is_registration_open', 'auto_accept'];

    public function eventMembers(): HasManyThrough
    {
        return $this->hasManyThrough(
            Member::class,
            EventMember::class,
            'event_id', //foreign key on the EventMember table
            'id',  //foreign key on the Member table
            'id', //local key on the event table
            'member_id' //local key on the EventMember table
        )->select(['members.id', 'members.first_name', 'members.last_name', 'members.username', 'members.telegram_id',
            'members.phone', 'members.company', 'event_members.is_activated', 'event_members.utm', 'members.email',
            'members.position', 'event_members.paid']);
    }

    public function speakers(): HasMany
    {
        return $this->hasMany(Speaker::class)->orderBy('start_at');
    }

    public function currentSpeaker(): HasMany
    {
        return $this->hasMany(Speaker::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(Field::class);
    }

    public function competitions(): HasMany
    {
        return $this->hasMany(Competition::class);
    }
}
