<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Validators\Failure;
use Throwable;

class ProductsImport implements
    ToCollection,
    WithHeadingRow,
    SkipsOnError,
    SkipsOnFailure,
    WithBatchInserts,
    WithChunkReading
{
    protected $successCount = 0;
    protected $errorCount = 0;
    protected $errors = [];
    protected $duplicateCount = 0;
    protected $updateCount = 0;

    public function collection(Collection $collection)
    {
        foreach ($collection as $index => $row) {
            try {
                // Clean and validate the row data
                $rowData = $this->cleanRowData($row->toArray());

                // Skip empty rows
                if ($this->isEmptyRow($rowData)) {
                    continue;
                }

                // Transform the data
                $transformedData = $this->transformData($rowData);

                // Validate the row
                $validator = $this->validateRow($transformedData, $index + 2); // +2 because of heading row and 0-based index

                if ($validator->fails()) {
                    $this->errorCount++;
                    $this->errors[] = [
                        'row' => $index + 2,
                        'errors' => $validator->errors()->all()
                    ];
                    continue;
                }

                $validatedData = $validator->validated();

                // Generate slug if not provided
                if (empty($validatedData['slug'])) {
                    $validatedData['slug'] = Str::slug($validatedData['name']);
                    
                    // Make sure auto-generated slug is unique
                    $originalSlug = $validatedData['slug'];
                    $counter = 1;
                    while (Product::where('slug', $validatedData['slug'])->exists()) {
                        $validatedData['slug'] = $originalSlug . '-' . $counter;
                        $counter++;
                    }
                }

                // Handle brand lookup
                if (!empty($validatedData['brand_name'])) {
                    $brand = Brand::where('name', $validatedData['brand_name'])->first();
                    if ($brand) {
                        $validatedData['brand_id'] = $brand->id;
                    } else {
                        $this->errorCount++;
                        $this->errors[] = [
                            'row' => $index + 2,
                            'errors' => ["Brand '{$validatedData['brand_name']}' not found"]
                        ];
                        continue;
                    }
                }
                unset($validatedData['brand_name']);

                // Handle category lookup
                if (!empty($validatedData['category_name'])) {
                    $category = Category::where('name', $validatedData['category_name'])->first();
                    if ($category) {
                        $validatedData['category_id'] = $category->id;
                    } else {
                        $this->errorCount++;
                        $this->errors[] = [
                            'row' => $index + 2,
                            'errors' => ["Category '{$validatedData['category_name']}' not found"]
                        ];
                        continue;
                    }
                }
                unset($validatedData['category_name']);

                // Check if product already exists by name or SKU
                $existingProduct = Product::where('name', $validatedData['name'])
                    ->orWhere('sku', $validatedData['sku'])
                    ->first();

                if ($existingProduct) {
                    // Update existing product
                    $existingProduct->update($validatedData);
                    $this->updateCount++;
                } else {
                    // Create new product
                    Product::create($validatedData);
                    $this->successCount++;
                }

            } catch (\Exception $e) {
                $this->errorCount++;
                $this->errors[] = [
                    'row' => $index + 2,
                    'errors' => ['Unexpected error: ' . $e->getMessage()]
                ];
            }
        }
    }

    /**
     * Clean row data by trimming whitespace and handling empty values
     */
    private function cleanRowData(array $row): array
    {
        return array_map(function ($value) {
            return is_string($value) ? trim($value) : $value;
        }, $row);
    }

    /**
     * Check if row is completely empty
     */
    private function isEmptyRow(array $row): bool
    {
        return empty(array_filter($row, function ($value) {
            return !empty($value);
        }));
    }

    /**
     * Validate individual row data
     */
    private function validateRow(array $row, int $rowNumber): \Illuminate\Validation\Validator
    {
        // Find existing product by name or SKU to get ID for uniqueness check
        $existingProductByName = Product::where('name', $row['name'] ?? '')->first();
        $existingProductBySku = Product::where('sku', $row['sku'] ?? '')->first();
        $existingProductBySlug = null;
        
        if (!empty($row['slug'])) {
            $existingProductBySlug = Product::where('slug', $row['slug'])->first();
        }

        return Validator::make($row, [
            'name' => [
                'required',
                'string',
                'max:255',
                // Only ignore uniqueness if we're updating the same product
                $existingProductByName ? 
                    Rule::unique('products', 'name')->ignore($existingProductByName->id) :
                    Rule::unique('products', 'name')
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                // Only ignore uniqueness if we're updating the same product
                $existingProductBySlug ? 
                    Rule::unique('products', 'slug')->ignore($existingProductBySlug->id) :
                    Rule::unique('products', 'slug')
            ],
            'description' => 'nullable|string|max:5000',
            'short_description' => 'nullable|string|max:500',
            'sku' => [
                'required',
                'string',
                'max:100',
                // Only ignore uniqueness if we're updating the same product
                $existingProductBySku ? 
                    Rule::unique('products', 'sku')->ignore($existingProductBySku->id) :
                    Rule::unique('products', 'sku')
            ],
            'price' => 'required|numeric|min:0|max:999999.99',
            'compare_price' => 'nullable|numeric|min:0|max:999999.99',
            'stock_quantity' => 'nullable|integer|min:0',
            'track_quantity' => 'nullable|boolean',
            'weight' => 'nullable|numeric|min:0|max:999999.99',
            'status' => 'required|string|in:active,inactive,draft',
            'brand_name' => 'nullable|string|max:255',
            'category_name' => 'nullable|string|max:255',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
        ], [
            'name.required' => "Product name is required for row {$rowNumber}",
            'name.unique' => "Product name already exists for row {$rowNumber}",
            'slug.unique' => "Product slug already exists for row {$rowNumber}",
            'slug.regex' => "Slug format is invalid for row {$rowNumber} (use lowercase letters, numbers, and hyphens only)",
            'sku.required' => "SKU is required for row {$rowNumber}",
            'sku.unique' => "SKU already exists for row {$rowNumber}",
            'price.required' => "Price is required for row {$rowNumber}",
            'price.numeric' => "Price must be a valid number for row {$rowNumber}",
            'price.min' => "Price must be greater than or equal to 0 for row {$rowNumber}",
            'status.required' => "Status is required for row {$rowNumber}",
            'status.in' => "Status must be active, inactive, or draft for row {$rowNumber}",
            'track_quantity.boolean' => "Track quantity value is invalid for row {$rowNumber}",
        ]);
    }

    /**
     * Transform the data before validation
     */
    private function transformData(array $data): array
    {
        // Convert track_quantity variations to boolean
        if (isset($data['track_quantity'])) {
            $trackQuantity = strtolower(trim((string)$data['track_quantity']));
            $data['track_quantity'] = in_array($trackQuantity, ['1', 'true', 'yes', 'track']);
        } else {
            $data['track_quantity'] = true; // Default to tracking quantity
        }

        // Handle dimensions - convert to array format
        $dimensions = [];
        if (!empty($data['width'])) {
            $dimensions['width'] = (float) $data['width'];
        }
        if (!empty($data['height'])) {
            $dimensions['height'] = (float) $data['height'];
        }
        if (!empty($data['length'])) {
            $dimensions['length'] = (float) $data['length'];
        }
        
        if (!empty($dimensions)) {
            $data['dimensions'] = $dimensions;
        }

        // Remove individual dimension fields
        unset($data['width'], $data['height'], $data['length']);

        // Handle status normalization
        if (isset($data['status'])) {
            $status = strtolower(trim((string)$data['status']));
            if (in_array($status, ['1', 'true', 'yes', 'published'])) {
                $data['status'] = 'active';
            } elseif (in_array($status, ['0', 'false', 'no', 'unpublished'])) {
                $data['status'] = 'inactive';
            } elseif (!in_array($status, ['active', 'inactive', 'draft'])) {
                $data['status'] = 'active'; // Default
            }
        } else {
            $data['status'] = 'active'; // Default
        }

        // Handle stock quantity default
        if (!isset($data['stock_quantity']) || $data['stock_quantity'] === '') {
            $data['stock_quantity'] = 0;
        }

        return $data;
    }

    /**
     * Handle errors during import
     */
    public function onError(Throwable $e)
    {
        $this->errorCount++;
        $this->errors[] = [
            'row' => 'Unknown',
            'errors' => ['Import error: ' . $e->getMessage()]
        ];
    }

    /**
     * Handle validation failures
     */
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errorCount++;
            $this->errors[] = [
                'row' => $failure->row(),
                'errors' => $failure->errors()
            ];
        }
    }

    /**
     * Get import results summary
     */
    public function getResults(): array
    {
        return [
            'success_count' => $this->successCount,
            'update_count' => $this->updateCount,
            'error_count' => $this->errorCount,
            'total_processed' => $this->successCount + $this->updateCount + $this->errorCount,
            'errors' => $this->errors
        ];
    }

    /**
     * Batch size for processing
     */
    public function batchSize(): int
    {
        return 50;
    }

    /**
     * Chunk size for reading
     */
    public function chunkSize(): int
    {
        return 50;
    }

    /**
     * Get expected headers for the Excel file
     */
    public static function getExpectedHeaders(): array
    {
        return [
            'name' => 'Product Name (Required)',
            'slug' => 'URL Slug (Optional - will be auto-generated)',
            'description' => 'Description (Optional)',
            'short_description' => 'Short Description (Optional)',
            'sku' => 'SKU/Product Code (Required - must be unique)',
            'price' => 'Price (Required - decimal format: 99.99)',
            'compare_price' => 'Compare Price (Optional - decimal format: 99.99)',
            'stock_quantity' => 'Stock Quantity (Optional - default: 0)',
            'track_quantity' => 'Track Quantity (Optional - 1/0, true/false, yes/no)',
            'weight' => 'Weight (Optional - decimal format: 0.50)',
            'width' => 'Width (Optional - decimal format)',
            'height' => 'Height (Optional - decimal format)',
            'length' => 'Length (Optional - decimal format)',
            'status' => 'Status (Required - active/inactive/draft)',
            'brand_name' => 'Brand Name (Optional - must match existing brand)',
            'category_name' => 'Category Name (Optional - must match existing category)'
        ];
    }

    /**
     * Generate sample data for template
     */
    public static function getSampleData(): array
    {
        return [
            [
                'iPhone 14 Pro', 
                'iphone-14-pro', 
                'Latest iPhone with Pro camera system and A16 Bionic chip', 
                'Latest iPhone with advanced features',
                'IPH14PRO128',
                '1299.99',
                '1399.99',
                '50',
                'true',
                '0.206',
                '71.5',
                '147.5',
                '7.85',
                'active',
                'Apple',
                'Smartphones'
            ],
            [
                'Samsung Galaxy S23', 
                '', 
                'Premium Android smartphone with exceptional camera', 
                'Premium Android smartphone',
                'SGS23256',
                '899.99',
                '',
                '25',
                '1',
                '0.168',
                '70.9',
                '146.3',
                '7.6',
                'active',
                'Samsung',
                'Smartphones'
            ],
            [
                'Nike Air Max 270', 
                'nike-air-max-270', 
                'Comfortable running shoes with Air Max technology', 
                'Comfortable running shoes',
                'NAM270BLK42',
                '159.99',
                '179.99',
                '0',
                'false',
                '0.5',
                '10',
                '30',
                '12',
                'active',
                'Nike',
                'Footwear'
            ],
            [
                'MacBook Pro 14"', 
                'macbook-pro-14', 
                'Professional laptop with M2 Pro chip', 
                'Professional laptop',
                'MBP14M2512',
                '2499.99',
                '',
                '15',
                'yes',
                '1.6',
                '31.26',
                '22.12',
                '1.55',
                'active',
                'Apple',
                'Laptops'
            ]
        ];
    }
}