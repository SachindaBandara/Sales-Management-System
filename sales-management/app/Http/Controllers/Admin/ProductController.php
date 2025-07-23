<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

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
                'products_without_images' => Product::where(function ($query) {
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

    /**
     * Export products to Excel
     */
    public function export(Request $request)
    {
        try {
            // Get filters from request
            $filters = [
                'search' => $request->get('search'),
                'status' => $request->get('status'),
                'brand_id' => $request->get('brand_id'),
                'category_id' => $request->get('category_id'),
                'stock_status' => $request->get('stock_status'),
                'price_min' => $request->get('price_min'),
                'price_max' => $request->get('price_max'),
            ];

            // Remove empty filters
            $filters = array_filter($filters, function ($value) {
                return $value !== null && $value !== '';
            });

            // Generate filename with timestamp
            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "products_export_{$timestamp}.xlsx";

            // Create and download Excel file
            return Excel::download(new ProductsExport($filters), $filename);
        } catch (\Exception $e) {
            Log::error('Product export failed: ' . $e->getMessage());

            return back()->with('error', 'Failed to export products. Please try again.');
        }
    }

    /**
     * Import products from Excel file
     */
    public function import(Request $request)
    {
        $request->validate([
            'import_file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:20480' // 20MB max file size for products (larger than brands)
            ]
        ], [
            'import_file.required' => 'Please select a file to import.',
            'import_file.mimes' => 'The file must be an Excel file (.xlsx, .xls) or CSV file (.csv).',
            'import_file.max' => 'The file size must not exceed 20MB.'
        ]);

        try {
            $import = new ProductsImport();

            Excel::import($import, $request->file('import_file'));

            $results = $import->getResults();

            // Prepare flash messages based on results
            $messages = [];

            if ($results['success_count'] > 0) {
                $messages[] = "Successfully imported {$results['success_count']} new products.";
            }

            if ($results['update_count'] > 0) {
                $messages[] = "Updated {$results['update_count']} existing products.";
            }

            if ($results['error_count'] > 0) {
                $messages[] = "Failed to process {$results['error_count']} rows due to errors.";
            }

            // If there are errors, store them in session for detailed view
            if (!empty($results['errors'])) {
                session(['import_errors' => $results['errors']]);

                return redirect()->route('admin.products.index')
                    ->with('warning', implode(' ', $messages))
                    ->with('import_results', $results);
            }

            return redirect()->route('admin.products.index')
                ->with('success', implode(' ', $messages))
                ->with('import_results', $results);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];

            foreach ($failures as $failure) {
                $errorMessages[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }

            return redirect()->route('admin.products.index')
                ->with('error', 'Import failed due to validation errors: ' . implode(' | ', array_slice($errorMessages, 0, 5)) . (count($errorMessages) > 5 ? '... and ' . (count($errorMessages) - 5) . ' more errors.' : ''));
        } catch (\Exception $e) {
            Log::error('Product import failed', [
                'error' => $e->getMessage(),
                'file' => $request->file('import_file')?->getClientOriginalName(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('admin.products.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download Excel template for product import
     */
    public function downloadTemplate()
    {
        try {
            $headers = ProductsImport::getExpectedHeaders();
            $sampleData = ProductsImport::getSampleData();

            // Create a CSV template with more detailed structure
            $filename = 'products_import_template_' . date('Y-m-d') . '.csv';
            $temp_file = tempnam(sys_get_temp_dir(), 'products_template');

            $handle = fopen($temp_file, 'w');

            // Write headers
            fputcsv($handle, array_keys($headers));

            // Write header descriptions as a comment row
            fputcsv($handle, array_values($headers));

            // Add separator row
            fputcsv($handle, array_fill(0, count($headers), '---'));

            // Write sample data
            foreach ($sampleData as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);

            return response()->download($temp_file, $filename, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Failed to generate products template', ['error' => $e->getMessage()]);

            return redirect()->route('admin.products.index')
                ->with('error', 'Failed to generate template file.');
        }
    }

    /**
     * Get import errors for display
     */
    public function getImportErrors()
    {
        $errors = session('import_errors', []);
        session()->forget('import_errors');

        return response()->json($errors);
    }
}
