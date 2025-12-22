<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletHistory extends Model
{
    protected $fillable = [
        'user_id',
        'payment_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
