<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot(){

        parent::boot();

        static::creating(function ($brand){
            if(empty($brand->slug)){
                $brand->slug = Str::slug($brand->name);
            }
        });

    }

      public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query){
        return $query->where('is_active', true);
    }

    public function getLogoAttribute($value)
    {
        if (!$value) {
            return asset('images/default-logo.png'); // Fallback to a default image
        }

        return filter_var($value, FILTER_VALIDATE_URL)
            ? $value
            : asset('storage/' . $value);
    }

}
