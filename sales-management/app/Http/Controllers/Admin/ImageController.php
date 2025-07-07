<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ImageController extends Controller
{
    /**
     * Upload multiple images for a product
     */
    public function upload(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $imagePaths = [];
            $existingImages = $product->images ?? [];

            foreach ($request->file('images') as $image) {
                // Generate unique filename
                $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

                // Store image
                $path = $image->storeAs('products', $filename, 'public');
                $imagePaths[] = $path;

                Log::info('Image uploaded', [
                    'product_id' => $product->id,
                    'original_name' => $image->getClientOriginalName(),
                    'stored_path' => $path
                ]);
            }

            // Merge with existing images
            $allImages = array_merge($existingImages, $imagePaths);

            // Update product with new images
            $product->update(['images' => $allImages]);

            return response()->json([
                'success' => true,
                'message' => 'Images uploaded successfully',
                'images' => $product->fresh()->image_urls,
                'uploaded_count' => count($imagePaths)
            ]);

        } catch (\Exception $e) {
            Log::error('Image upload failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload images'
            ], 500);
        }
    }

    /**
     * Delete a specific image from product
     */
    public function delete(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'image_path' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Image path is required',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $imagePath = $request->input('image_path');
            $currentImages = $product->images ?? [];

            // Check if image exists in product's images
            if (!in_array($imagePath, $currentImages)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image not found in product'
                ], 404);
            }

            // Remove image from storage
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            // Remove image from product's images array
            $updatedImages = array_values(array_filter($currentImages, function($image) use ($imagePath) {
                return $image !== $imagePath;
            }));

            $product->update(['images' => $updatedImages]);

            Log::info('Image deleted', [
                'product_id' => $product->id,
                'deleted_image' => $imagePath
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully',
                'images' => $product->fresh()->image_urls
            ]);

        } catch (\Exception $e) {
            Log::error('Image deletion failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image'
            ], 500);
        }
    }

    /**
     * Reorder product images
     */
    public function reorder(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array',
            'images.*' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid image order data',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $newOrder = $request->input('images');
            $currentImages = $product->images ?? [];

            // Validate that all provided images exist in current images
            foreach ($newOrder as $imagePath) {
                if (!in_array($imagePath, $currentImages)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid image in reorder list'
                    ], 422);
                }
            }

            // Update product with new image order
            $product->update(['images' => $newOrder]);

            Log::info('Images reordered', [
                'product_id' => $product->id,
                'new_order' => $newOrder
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Images reordered successfully',
                'images' => $product->fresh()->image_urls
            ]);

        } catch (\Exception $e) {
            Log::error('Image reordering failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder images'
            ], 500);
        }
    }

    /**
     * Get all images for a product
     */
    public function index(Product $product)
    {
        try {
            return response()->json([
                'success' => true,
                'images' => $product->image_urls,
                'total_images' => count($product->images ?? [])
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch product images', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch images'
            ], 500);
        }
    }

    /**
     * Delete all images for a product
     */
    public function deleteAll(Product $product)
    {
        try {
            $currentImages = $product->images ?? [];

            // Delete all images from storage
            foreach ($currentImages as $imagePath) {
                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
            }

            // Clear images from product
            $product->update(['images' => []]);

            Log::info('All images deleted', [
                'product_id' => $product->id,
                'deleted_count' => count($currentImages)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'All images deleted successfully',
                'deleted_count' => count($currentImages)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete all images', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete all images'
            ], 500);
        }
    }

    /**
     * Set primary image for product
     */
    public function setPrimary(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'image_path' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Image path is required',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $imagePath = $request->input('image_path');
            $currentImages = $product->images ?? [];

            // Check if image exists in product's images
            if (!in_array($imagePath, $currentImages)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image not found in product'
                ], 404);
            }

            // Remove the image from current position
            $filteredImages = array_filter($currentImages, function($image) use ($imagePath) {
                return $image !== $imagePath;
            });

            // Add it to the beginning
            $reorderedImages = array_merge([$imagePath], array_values($filteredImages));

            $product->update(['images' => $reorderedImages]);

            Log::info('Primary image set', [
                'product_id' => $product->id,
                'primary_image' => $imagePath
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Primary image set successfully',
                'images' => $product->fresh()->image_urls
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to set primary image', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to set primary image'
            ], 500);
        }
    }
}
