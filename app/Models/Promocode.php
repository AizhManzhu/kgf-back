<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Promocode extends Model
{
    use HasFactory;
    const MULTI = "multi";
    const SINGLE = "single";

    protected $fillable = [
        'code',
        'max_try',
        'is_activated',
        'has_access',
        'type'
    ];

    public function usedMember(): HasOneThrough
    {
        return $this->hasOneThrough(Member::class, EventMember::class, 'promocode_id', 'id', 'id', 'member_id');
    }
}
