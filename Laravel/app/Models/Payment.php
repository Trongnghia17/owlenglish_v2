<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'package_id',
        'order_code',
        'amount',
        'currency',
        'status',
        'payment_method',
        'payos_payment_id',
        'payos_data',
        'paid_at',
    ];

    protected $casts = [
        'payos_data' => 'array',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(PaymentPackage::class, 'package_id');
    }
}
