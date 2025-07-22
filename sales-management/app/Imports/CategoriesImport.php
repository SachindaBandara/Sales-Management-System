<?php

namespace App\Imports;

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

class CategoriesImport implements
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
                    while (Category::where('slug', $validatedData['slug'])->exists()) {
                        $validatedData['slug'] = $originalSlug . '-' . $counter;
                        $counter++;
                    }
                }

                // Handle parent category
                if (!empty($validatedData['parent_name'])) {
                    $parent = Category::where('name', $validatedData['parent_name'])->first();
                    if ($parent) {
                        $validatedData['parent_id'] = $parent->id;
                    } else {
                        $this->errorCount++;
                        $this->errors[] = [
                            'row' => $index + 2,
                            'errors' => ["Parent category '{$validatedData['parent_name']}' not found"]
                        ];
                        continue;
                    }
                }
                
                // Remove parent_name from data as it's not a database field
                unset($validatedData['parent_name']);

                // Check if category already exists by name or slug
                $existingCategory = Category::where('name', $validatedData['name'])
                    ->orWhere('slug', $validatedData['slug'])
                    ->first();

                if ($existingCategory) {
                    // Update existing category
                    $existingCategory->update($validatedData);
                    $this->updateCount++;
                } else {
                    // Create new category
                    Category::create($validatedData);
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
        // Find existing category by name to get ID for uniqueness check
        $existingCategoryByName = Category::where('name', $row['name'] ?? '')->first();
        $existingCategoryBySlug = null;
        
        if (!empty($row['slug'])) {
            $existingCategoryBySlug = Category::where('slug', $row['slug'])->first();
        }

        return Validator::make($row, [
            'name' => [
                'required',
                'string',
                'max:255',
                // Only ignore uniqueness if we're updating the same category
                $existingCategoryByName ? 
                    Rule::unique('categories', 'name')->ignore($existingCategoryByName->id) :
                    Rule::unique('categories', 'name')
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                // Only ignore uniqueness if we're updating the same category
                $existingCategoryBySlug ? 
                    Rule::unique('categories', 'slug')->ignore($existingCategoryBySlug->id) :
                    Rule::unique('categories', 'slug')
            ],
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|string|url|max:500',
            'parent_name' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean'
        ], [
            'name.required' => "Name is required for row {$rowNumber}",
            'name.unique' => "Category name already exists for row {$rowNumber}",
            'slug.unique' => "Slug already exists for row {$rowNumber}",
            'slug.regex' => "Slug format is invalid for row {$rowNumber} (use lowercase letters, numbers, and hyphens only)",
            'image.url' => "Image URL format is invalid for row {$rowNumber}",
            'sort_order.integer' => "Sort order must be a number for row {$rowNumber}",
            'sort_order.min' => "Sort order cannot be negative for row {$rowNumber}",
            'sort_order.max' => "Sort order cannot exceed 9999 for row {$rowNumber}",
            'is_active.boolean' => "Status value is invalid for row {$rowNumber}"
        ]);
    }

    /**
     * Transform the data before validation
     */
    private function transformData(array $data): array
    {
        // Convert is_active variations to boolean
        if (isset($data['is_active'])) {
            $status = strtolower(trim((string)$data['is_active']));
            $data['is_active'] = in_array($status, ['1', 'true', 'yes', 'active']);
        } else {
            $data['is_active'] = true; // Default to active
        }

        // Handle image URL - rename image_url to image for database consistency
        if (isset($data['image_url']) && !empty($data['image_url'])) {
            $data['image'] = $data['image_url'];
        }
        unset($data['image_url']);

        // Set default sort_order if not provided
        if (!isset($data['sort_order']) || $data['sort_order'] === '' || $data['sort_order'] === null) {
            $data['sort_order'] = 0;
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
        return 100;
    }

    /**
     * Chunk size for reading
     */
    public function chunkSize(): int
    {
        return 100;
    }

    /**
     * Get expected headers for the Excel file
     */
    public static function getExpectedHeaders(): array
    {
        return [
            'name' => 'Category Name (Required)',
            'slug' => 'URL Slug (Optional - will be auto-generated)',
            'description' => 'Description (Optional)',
            'image_url' => 'Image URL (Optional)',
            'parent_name' => 'Parent Category Name (Optional)',
            'sort_order' => 'Sort Order (Optional - Default: 0)',
            'is_active' => 'Status (Optional - 1/0, true/false, yes/no, active/inactive)'
        ];
    }

    /**
     * Generate sample data for template
     */
    public static function getSampleData(): array
    {
        return [
            ['Electronics', 'electronics', 'Electronic devices and accessories', 'https://example.com/electronics.jpg', '', '1', 'active'],
            ['Mobile Phones', 'mobile-phones', 'Smartphones and accessories', 'https://example.com/phones.jpg', 'Electronics', '1', '1'],
            ['Laptops', 'laptops', 'Laptop computers and accessories', 'https://example.com/laptops.jpg', 'Electronics', '2', 'true'],
            ['Clothing', 'clothing', 'Men and women clothing', '', '', '2', 'yes'],
            ['Men\'s Clothing', 'mens-clothing', 'Clothing for men', 'https://example.com/mens.jpg', 'Clothing', '1', '1'],
            ['Women\'s Clothing', 'womens-clothing', 'Clothing for women', 'https://example.com/womens.jpg', 'Clothing', '2', 'active'],
        ];
    }
}