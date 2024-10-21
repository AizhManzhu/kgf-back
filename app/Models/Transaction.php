<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'payment_system',
        'order_id',
        'amount',
        'status',
        'external_transaction_id',
        'payment_date'
    ];

    protected $dates = ['payment_date'];

    public function eventMember(): HasOne
    {
        return $this->hasOne(
            EventMember::class,
            'id',
            'order_id'
        );
    }

}
