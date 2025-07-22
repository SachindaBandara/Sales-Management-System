<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
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

class UsersImport implements
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

                // Check if user already exists by email
                $existingUser = User::where('email', $validatedData['email'])->first();

                if ($existingUser) {
                    // Update existing user (excluding password if empty)
                    $updateData = $validatedData;
                    if (empty($updateData['password'])) {
                        unset($updateData['password']);
                    }
                    $existingUser->update($updateData);
                    $this->updateCount++;
                } else {
                    // Create new user
                    User::create($validatedData);
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
        // Find existing user by email to get ID for uniqueness check
        $existingUser = User::where('email', $row['email'] ?? '')->first();

        return Validator::make($row, [
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                // Only ignore uniqueness if we're updating the same user
                $existingUser ? 
                    Rule::unique('users', 'email')->ignore($existingUser->id) :
                    Rule::unique('users', 'email')
            ],
            'password' => [
                // Password is required only for new users
                !$existingUser ? 'required' : 'nullable',
                'string',
                'min:8'
            ],
            'role' => [
                'required',
                'string',
                Rule::in(['admin', 'customer'])
            ],
            'is_active' => 'nullable|boolean',
            'last_login_at' => 'nullable|date'
        ], [
            'name.required' => "Name is required for row {$rowNumber}",
            'email.required' => "Email is required for row {$rowNumber}",
            'email.unique' => "Email already exists for row {$rowNumber}",
            'email.email' => "Email format is invalid for row {$rowNumber}",
            'password.required' => "Password is required for new users in row {$rowNumber}",
            'password.min' => "Password must be at least 8 characters for row {$rowNumber}",
            'role.required' => "Role is required for row {$rowNumber}",
            'role.in' => "Role must be either 'admin' or 'customer' for row {$rowNumber}",
            'is_active.boolean' => "Status value is invalid for row {$rowNumber}",
            'last_login_at.date' => "Last login date format is invalid for row {$rowNumber}"
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

        // Normalize role to lowercase
        if (isset($data['role'])) {
            $data['role'] = strtolower(trim($data['role']));
        }

        // Hash password if provided
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Handle date formatting for last_login_at
        if (!empty($data['last_login_at'])) {
            try {
                $data['last_login_at'] = \Carbon\Carbon::parse($data['last_login_at']);
            } catch (\Exception $e) {
                // Let validation handle the error
            }
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
            'name' => 'Full Name (Required)',
            'email' => 'Email Address (Required)',
            'password' => 'Password (Required for new users, min 8 characters)',
            'role' => 'Role (Required - admin/customer)',
            'is_active' => 'Status (Optional - 1/0, true/false, yes/no, active/inactive)',
            'last_login_at' => 'Last Login Date (Optional - YYYY-MM-DD HH:MM:SS)'
        ];
    }

    /**
     * Generate sample data for template
     */
    public static function getSampleData(): array
    {
        return [
            ['John Doe', 'john.doe@example.com', 'password123', 'customer', 'active', '2024-01-15 10:30:00'],
            ['Jane Smith', 'jane.smith@example.com', 'securepass456', 'admin', '1', '2024-01-20 14:45:00'],
            ['Bob Johnson', 'bob.johnson@example.com', 'mypassword789', 'customer', 'true', ''],
            ['Alice Brown', 'alice.brown@example.com', 'strongpass321', 'customer', 'yes', '2024-01-18 09:15:00'],
        ];
    }
}