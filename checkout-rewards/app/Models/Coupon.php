<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    protected $fillable = [
        'milestone_order_count',
        'discount_percent',
        'status',
        'redeemed_order_id',
        'redeemed_at',
    ];

    protected function casts(): array
    {
        return [
            'milestone_order_count' => 'integer',
            'discount_percent' => 'integer',
            'redeemed_order_id' => 'integer',
            'redeemed_at' => 'datetime',
        ];
    }

    public function redeemedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'redeemed_order_id');
    }
}