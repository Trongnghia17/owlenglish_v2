<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserContact extends Model
{
    protected $fillable = [
        'user_id', 'type', 'value', 'is_primary', 'verified_at',
    ];

    protected $casts = [
        'is_primary'  => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Giữ email thường hoá */
    public function setValueAttribute($value): void
    {
        if (($this->attributes['type'] ?? $this->type) === 'email') {
            $this->attributes['value'] = $value ? mb_strtolower(trim($value)) : null;
        } else {
            $this->attributes['value'] = trim($value);
        }
    }
}
