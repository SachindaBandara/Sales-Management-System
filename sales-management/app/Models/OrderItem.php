<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_sku',
        'product_price',
        'quantity',
        'total_price',
        'product_snapshot',
    ];

    protected $casts = [
        'product_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'product_snapshot' => 'array',
    ];

    protected $appends = [
        'formatted_price',
        'formatted_total',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'LKR' . number_format($this->product_price, 2);
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'LKR' . number_format($this->total_price, 2);
    }

    public function getProductImageAttribute(): string
    {
        if ($this->product_snapshot && isset($this->product_snapshot['image'])) {
            return $this->product_snapshot['image'];
        }

        return $this->product?->first_image_url ?? '/images/placeholder.jpg';
    }
}
