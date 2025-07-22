<?php

namespace App\Imports;

use App\Models\Brand;
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

class BrandsImport implements
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
                    while (Brand::where('slug', $validatedData['slug'])->exists()) {
                        $validatedData['slug'] = $originalSlug . '-' . $counter;
                        $counter++;
                    }
                }

                // Check if brand already exists by name or slug
                $existingBrand = Brand::where('name', $validatedData['name'])
                    ->orWhere('slug', $validatedData['slug'])
                    ->first();

                if ($existingBrand) {
                    // Update existing brand
                    $existingBrand->update($validatedData);
                    $this->updateCount++;
                } else {
                    // Create new brand
                    Brand::create($validatedData);
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
        // Find existing brand by name to get ID for uniqueness check
        $existingBrandByName = Brand::where('name', $row['name'] ?? '')->first();
        $existingBrandBySlug = null;
        
        if (!empty($row['slug'])) {
            $existingBrandBySlug = Brand::where('slug', $row['slug'])->first();
        }

        return Validator::make($row, [
            'name' => [
                'required',
                'string',
                'max:255',
                // Only ignore uniqueness if we're updating the same brand
                $existingBrandByName ? 
                    Rule::unique('brands', 'name')->ignore($existingBrandByName->id) :
                    Rule::unique('brands', 'name')
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                // Only ignore uniqueness if we're updating the same brand
                $existingBrandBySlug ? 
                    Rule::unique('brands', 'slug')->ignore($existingBrandBySlug->id) :
                    Rule::unique('brands', 'slug')
            ],
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|string|url|max:500',
            'is_active' => 'nullable|boolean'
        ], [
            'name.required' => "Name is required for row {$rowNumber}",
            'name.unique' => "Brand name already exists for row {$rowNumber}",
            'slug.unique' => "Slug already exists for row {$rowNumber}",
            'slug.regex' => "Slug format is invalid for row {$rowNumber} (use lowercase letters, numbers, and hyphens only)",
            'logo.url' => "Logo URL format is invalid for row {$rowNumber}",
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

        // Handle logo URL - rename logo_url to logo for database consistency
        if (isset($data['logo_url']) && !empty($data['logo_url'])) {
            $data['logo'] = $data['logo_url'];
        }
        unset($data['logo_url']);

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
            'name' => 'Brand Name (Required)',
            'slug' => 'URL Slug (Optional - will be auto-generated)',
            'description' => 'Description (Optional)',
            'logo_url' => 'Logo URL (Optional)',
            'is_active' => 'Status (Optional - 1/0, true/false, yes/no, active/inactive)'
        ];
    }

    /**
     * Generate sample data for template
     */
    public static function getSampleData(): array
    {
        return [
            ['Apple', 'apple', 'Technology company known for innovative products', 'https://example.com/apple-logo.png', 'active'],
            ['Samsung', 'samsung', 'South Korean multinational conglomerate', 'https://example.com/samsung-logo.png', '1'],
            ['Nike', '', 'American multinational corporation', '', 'true'],
            ['Adidas', 'adidas-brand', 'German multinational corporation', 'https://example.com/adidas-logo.png', 'yes'],
        ];
    }
}