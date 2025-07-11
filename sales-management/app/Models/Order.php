<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'payment_status',
        'payment_method',
        'billing_address',
        'shipping_address',
        'notes',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    protected $appends = [
        'status_badge',
        'payment_status_badge',
        'formatted_total',
        'items_count',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'pending' => ['color' => 'yellow', 'text' => 'Pending'],
            'processing' => ['color' => 'blue', 'text' => 'Processing'],
            'shipped' => ['color' => 'purple', 'text' => 'Shipped'],
            'delivered' => ['color' => 'green', 'text' => 'Delivered'],
            'cancelled' => ['color' => 'red', 'text' => 'Cancelled'],
            default => ['color' => 'gray', 'text' => 'Unknown'],
        };
    }

    public function getPaymentStatusBadgeAttribute(): array
    {
        return match($this->payment_status) {
            'pending' => ['color' => 'yellow', 'text' => 'Pending'],
            'paid' => ['color' => 'green', 'text' => 'Paid'],
            'failed' => ['color' => 'red', 'text' => 'Failed'],
            'refunded' => ['color' => 'orange', 'text' => 'Refunded'],
            default => ['color' => 'gray', 'text' => 'Unknown'],
        };
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'LKR' . number_format($this->total_amount, 2);
    }

    public function getItemsCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $timestamp = now()->format('Ymd');
        $random = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        return $prefix . $timestamp . $random;
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPaymentStatus($query, $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }
}
