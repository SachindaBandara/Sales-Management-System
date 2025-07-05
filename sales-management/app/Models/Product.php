<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
   use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'sku',
        'price',
        'compare_price',
        'stock_quantity',
        'track_quantity',
        'images',
        'weight',
        'dimensions',
        'status',
        'brand_id',
        'category_id',
        'meta_data',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'images' => 'array',
        'dimensions' => 'array',
        'meta_data' => 'array',
        'track_quantity' => 'boolean',
    ];
      protected $appends = [
        'image_urls',
        'first_image_url',
        'discount_percentage',
        'is_in_stock'
    ];

 protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getDiscountPercentageAttribute()
    {
        if ($this->compare_price && $this->compare_price > $this->price) {
            return round((($this->compare_price - $this->price) / $this->compare_price) * 100);
        }
        return 0;
    }

    public function getIsInStockAttribute()
    {
        if (!$this->track_quantity) {
            return true;
        }
        return $this->stock_quantity > 0;
    }

    // Accessor to get full image URLs
    public function getImageUrlsAttribute()
    {
        // Handle null or empty images
        if (!$this->images) {
            return [];
        }

        // Handle case where images is stored as JSON string
        $images = $this->images;
        if (is_string($images)) {
            $images = json_decode($images, true);
        }

        // Ensure we have an array
        if (!is_array($images)) {
            return [];
        }

        // Filter out empty values and create URLs
        return array_map(function($image) {
            return asset('storage/' . $image);
        }, array_filter($images));
    }

    // Get first image URL
    public function getFirstImageUrlAttribute()
    {
        $urls = $this->image_urls;
        return !empty($urls) ? $urls[0] : asset('images/placeholder.jpg');
    }

    // Method to get thumbnail URL (optional - for performance)
    public function getThumbnailUrlAttribute()
    {
        return $this->first_image_url;
    }
}
