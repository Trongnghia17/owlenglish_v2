<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentPackage extends Model
{
    use HasFactory;

    protected $table = 'payment_packages';

    protected $fillable = [
        'name',
        'duration',
        'price',
        'discount_percent',
        'final_price',
        'is_featured',
        'display_order',
        'status',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'status'      => 'integer',
    ];

    /**
     * Scope: chỉ lấy gói đang hiển thị
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
