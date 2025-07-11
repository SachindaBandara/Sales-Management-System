<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'is_in_stock',
        'image_count',
        'formatted_price',
        'formatted_compare_price',
        'stock_status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });

        // Clean up images when product is deleted
        static::deleting(function ($product) {
            $product->deleteAllImages();
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

    public function getImageCountAttribute()
    {
        return count($this->images ?? []);
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

    // Enhanced image management methods

    /**
     * Add images to product
     */
    public function addImages(array $imagePaths)
    {
        $currentImages = $this->images ?? [];
        $newImages = array_merge($currentImages, $imagePaths);

        $this->update(['images' => $newImages]);

        return $this;
    }

    /**
     * Remove specific image from product
     */
    public function removeImage($imagePath)
    {
        $currentImages = $this->images ?? [];

        // Remove from storage
        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }

        // Remove from array
        $updatedImages = array_values(array_filter($currentImages, function($image) use ($imagePath) {
            return $image !== $imagePath;
        }));

        $this->update(['images' => $updatedImages]);

        return $this;
    }

    /**
     * Delete all images for product
     */
    public function deleteAllImages()
    {
        $currentImages = $this->images ?? [];

        // Delete from storage
        foreach ($currentImages as $imagePath) {
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
        }

        // Clear from database
        $this->update(['images' => []]);

        return $this;
    }

    /**
     * Set primary image (move to first position)
     */
    public function setPrimaryImage($imagePath)
    {
        $currentImages = $this->images ?? [];

        if (!in_array($imagePath, $currentImages)) {
            return false;
        }

        // Remove from current position
        $filteredImages = array_filter($currentImages, function($image) use ($imagePath) {
            return $image !== $imagePath;
        });

        // Add to beginning
        $reorderedImages = array_merge([$imagePath], array_values($filteredImages));

        $this->update(['images' => $reorderedImages]);

        return $this;
    }

    /**
     * Reorder images
     */
    public function reorderImages(array $newOrder)
    {
        $currentImages = $this->images ?? [];

        // Validate that all images in new order exist in current images
        foreach ($newOrder as $imagePath) {
            if (!in_array($imagePath, $currentImages)) {
                return false;
            }
        }

        $this->update(['images' => $newOrder]);

        return $this;
    }

    /**
     * Get image at specific index
     */
    public function getImageAtIndex($index)
    {
        $urls = $this->image_urls;
        return isset($urls[$index]) ? $urls[$index] : null;
    }

    /**
     * Check if product has images
     */
    public function hasImages()
    {
        return !empty($this->images);
    }

    /**
     * Get image information with metadata
     */
    public function getImageInfo()
    {
        $images = $this->images ?? [];
        $info = [];

        foreach ($images as $index => $imagePath) {
            $fullPath = storage_path('app/public/' . $imagePath);

            $info[] = [
                'path' => $imagePath,
                'url' => asset('storage/' . $imagePath),
                'index' => $index,
                'is_primary' => $index === 0,
                'exists' => file_exists($fullPath),
                'size' => file_exists($fullPath) ? filesize($fullPath) : 0,
            ];
        }

        return $info;
    }

    //Order management methods can be added here as needed
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'LKR' . number_format($this->price, 2);
    }

    public function getFormattedComparePriceAttribute(): string
    {
        return $this->compare_price ? '$' . number_format($this->compare_price, 2) : '';
    }

    public function getStockStatusAttribute(): string
    {
        if (!$this->track_quantity) {
            return 'In Stock';
        }

        if ($this->stock_quantity <= 0) {
            return 'Out of Stock';
        }

        if ($this->stock_quantity <= 10) {
            return 'Low Stock';
        }

        return 'In Stock';
    }

    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->where('track_quantity', false)
              ->orWhere('stock_quantity', '>', 0);
        });
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByBrand($query, $brandId)
    {
        return $query->where('brand_id', $brandId);
    }

    public function decreaseStock($quantity): bool
    {
        if (!$this->track_quantity) {
            return true;
        }

        if ($this->stock_quantity >= $quantity) {
            $this->decrement('stock_quantity', $quantity);
            return true;
        }

        return false;
    }

    public function increaseStock($quantity): void
    {
        if ($this->track_quantity) {
            $this->increment('stock_quantity', $quantity);
        }
    }

    public function getTotalSoldAttribute(): int
    {
        return $this->orderItems()->whereHas('order', function ($query) {
            $query->where('payment_status', 'paid');
        })->sum('quantity');
    }

    public function getRevenueAttribute(): float
    {
        return $this->orderItems()->whereHas('order', function ($query) {
            $query->where('payment_status', 'paid');
        })->sum('total_price');
    }
}
