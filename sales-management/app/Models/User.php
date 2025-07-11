<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'last_login_at',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Role helper methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }
    
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    // Scopes
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeCustomers($query)
    {
        return $query->where('role', 'customer');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    //Orders relationship
     public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // public function getFullAddressAttribute(): string
    // {
    //     if (is_array($this->address)) {
    //         return implode(', ', array_filter([
    //             $this->address['street'] ?? '',
    //             $this->city,
    //             $this->state,
    //             $this->postal_code,
    //             $this->country,
    //         ]));
    //     }

    //     return implode(', ', array_filter([
    //         $this->address,
    //         $this->city,
    //         $this->state,
    //         $this->postal_code,
    //         $this->country,
    //     ]));
    // }

    public function getIsCustomerAttribute(): bool
    {
        return !$this->is_admin;
    }

    public function getOrdersCountAttribute(): int
    {
        return $this->orders()->count();
    }

    public function getTotalSpentAttribute(): float
    {
        return $this->orders()->where('payment_status', 'paid')->sum('total_amount');
    }

    public function getFormattedTotalSpentAttribute(): string
    {
        return '$' . number_format($this->total_spent, 2);
    }

    public function hasOrders(): bool
    {
        return $this->orders()->exists();
    }

    public function recentOrders($limit = 5)
    {
        return $this->orders()->with('items.product')->latest()->limit($limit)->get();
    }
}
