<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'cart_id',
        'idempotency_key',
        'gross_cents',
        'discount_cents',
        'net_cents',
        'placed_at',
    ];

    protected function casts(): array
    {
        return [
            'gross_cents' => 'integer',
            'discount_cents' => 'integer',
            'net_cents' => 'integer',
            'placed_at' => 'datetime',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}