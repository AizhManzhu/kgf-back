<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EventMember extends Model
{
    use HasFactory;
    protected $table = 'event_members';
    protected $fillable = ['event_id', 'member_id', 'is_activated', 'is_registered', 'utm', 'paid',
        'is_online', 'need_mentor', 'is_sponsor'];

    public function event(): HasOne
    {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }

    public function member(): HasOne
    {
        return $this->hasOne(Member::class, 'id', 'member_id');
    }

    public function promocode(): HasOne
    {
        return $this->hasOne(Promocode::class, 'id', 'promocode_id');
    }
}
