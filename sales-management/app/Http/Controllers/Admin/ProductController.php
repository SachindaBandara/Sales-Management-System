<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = Product::query()
            ->with(['brand', 'category'])
            // Apply filters based on request parameters
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            })
            // Filter by status
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            // Filter by brand
            ->when($request->brand_id, function ($query, $brandId) {
                $query->where('brand_id', $brandId);
            })
            // Filter by category
            ->when($request->category_id, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Transform the products to include image information
        $products->getCollection()->transform(function ($product) {
            // These accessors are already appended in the model
            return $product;
        });
        
        // Get active brands and categories for filters
        $brands = Brand::active()->orderBy('name')->get();
        // Get active categories for filters
        $categories = Category::active()->orderBy('name')->get();

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'brands' => $brands,
            'categories' => $categories,
            'filters' => $request->only(['search', 'status', 'brand_id', 'category_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $brands = Brand::active()->orderBy('name')->get();
        $categories = Category::active()->orderBy('name')->get();

        return Inertia::render('Admin/Products/Create', [
            'brands' => $brands,
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'sku' => 'required|string|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'track_quantity' => 'boolean',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|array',
            'dimensions.length' => 'nullable|numeric|min:0',
            'dimensions.width' => 'nullable|numeric|min:0',
            'dimensions.height' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,draft,archived',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'meta_data' => 'nullable|array',
        ]);

        try {
            // Create product without images first
            $product = Product::create($validated);

            Log::info('Product created', [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku
            ]);

            // Note: Images should be uploaded separately using the ImageController
            // This allows for better handling of image uploads and more flexibility

            return redirect()->route('admin.products.index')
                ->with('success', 'Product created successfully. You can now upload images using the image management section.');

        } catch (\Exception $e) {
            Log::error('Product creation failed', [
                'error' => $e->getMessage(),
                'validated_data' => $validated
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to create product. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load(['brand', 'category']);

        return Inertia::render('Admin/Products/Show', [
            'product' => $product,
            'image_info' => $product->getImageInfo(), // Enhanced image information
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $brands = Brand::active()->orderBy('name')->get();
        $categories = Category::active()->orderBy('name')->get();

        return Inertia::render('Admin/Products/Edit', [
            'product' => $product,
            'brands' => $brands,
            'categories' => $categories,
            'image_info' => $product->getImageInfo(), // Enhanced image information
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'track_quantity' => 'boolean',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|array',
            'dimensions.length' => 'nullable|numeric|min:0',
            'dimensions.width' => 'nullable|numeric|min:0',
            'dimensions.height' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,draft,archived',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'meta_data' => 'nullable|array',
        ]);

        try {
            // Update product data (excluding images)
            $product->update($validated);

            Log::info('Product updated', [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku
            ]);

            // Note: Images should be managed separately using the ImageController
            // This provides better control and error handling for image operations

            return redirect()->route('admin.products.index')
                ->with('success', 'Product updated successfully.');

        } catch (\Exception $e) {
            Log::error('Product update failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'validated_data' => $validated
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to update product. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            $productId = $product->id;
            $productName = $product->name;

            // Delete the product (images will be automatically deleted via model event)
            $product->delete();

            Log::info('Product deleted', [
                'product_id' => $productId,
                'name' => $productName
            ]);

            return redirect()->route('admin.products.index')
                ->with('success', 'Product and all associated images deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Product deletion failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete product. Please try again.']);
        }
    }

    /**
     * Toggle product status
     */
    public function toggleStatus(Product $product)
    {
        try {
            $newStatus = $product->status === 'active' ? 'draft' : 'active';
            $product->update(['status' => $newStatus]);

            Log::info('Product status toggled', [
                'product_id' => $product->id,
                'old_status' => $product->status,
                'new_status' => $newStatus
            ]);

            return redirect()->back()
                ->with('success', "Product status changed to {$newStatus}.");

        } catch (\Exception $e) {
            Log::error('Product status toggle failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to toggle product status.']);
        }
    }

    /**
     * Duplicate a product
     */
    public function duplicate(Product $product)
    {
        try {
            // Create a copy of the product
            $newProduct = $product->replicate();
            $newProduct->name = $product->name . ' (Copy)';
            $newProduct->sku = $product->sku . '-copy-' . time();
            $newProduct->status = 'draft';
            $newProduct->images = []; // Don't copy images
            $newProduct->save();

            Log::info('Product duplicated', [
                'original_product_id' => $product->id,
                'new_product_id' => $newProduct->id
            ]);

            return redirect()->route('admin.products.edit', $newProduct)
                ->with('success', 'Product duplicated successfully. Images were not copied - you can upload new images separately.');

        } catch (\Exception $e) {
            Log::error('Product duplication failed', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to duplicate product.']);
        }
    }

    /**
     * Get product statistics
     */
    public function statistics()
    {
        try {
            $stats = [
                'total_products' => Product::count(),
                'active_products' => Product::where('status', 'active')->count(),
                'draft_products' => Product::where('status', 'draft')->count(),
                'archived_products' => Product::where('status', 'archived')->count(),
                'out_of_stock' => Product::where('track_quantity', true)
                    ->where('stock_quantity', '<=', 0)->count(),
                'products_with_images' => Product::whereNotNull('images')
                    ->where('images', '!=', '[]')->count(),
                'products_without_images' => Product::where(function($query) {
                    $query->whereNull('images')
                        ->orWhere('images', '[]');
                })->count(),
            ];

            return response()->json([
                'success' => true,
                'statistics' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get product statistics', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics'
            ], 500);
        }
    }

    /**
     * Bulk operations on products
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:delete,activate,deactivate,archive',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id'
        ]);

        try {
            $products = Product::whereIn('id', $validated['product_ids']);
            $count = $products->count();

            switch ($validated['action']) {
                case 'delete':
                    $products->delete();
                    $message = "{$count} products deleted successfully.";
                    break;

                case 'activate':
                    $products->update(['status' => 'active']);
                    $message = "{$count} products activated successfully.";
                    break;

                case 'deactivate':
                    $products->update(['status' => 'draft']);
                    $message = "{$count} products deactivated successfully.";
                    break;

                case 'archive':
                    $products->update(['status' => 'archived']);
                    $message = "{$count} products archived successfully.";
                    break;
            }

            Log::info('Bulk action performed', [
                'action' => $validated['action'],
                'product_ids' => $validated['product_ids'],
                'count' => $count
            ]);

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Bulk action failed', [
                'action' => $validated['action'],
                'product_ids' => $validated['product_ids'],
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Bulk action failed. Please try again.']);
        }
    }
}
