<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;

class OrderCsvValidator
{
    public static function validateHeaders(array $headers): array
    {
        $requiredHeaders = [
            'Order Number',
            'Customer Email',
            'Customer Name',
            'Status',
            'Payment Status',
        ];

        $optionalHeaders = [
            'Subtotal',
            'Tax Amount',
            'Shipping Amount',
            'Total Amount',
            'Notes',
        ];

        $allowedHeaders = array_merge($requiredHeaders, $optionalHeaders);

        $errors = [];

        // Check for required headers
        foreach ($requiredHeaders as $required) {
            if (!in_array($required, $headers)) {
                $errors[] = "Missing required header: {$required}";
            }
        }

        // Check for unknown headers
        foreach ($headers as $header) {
            if (!in_array($header, $allowedHeaders)) {
                $errors[] = "Unknown header: {$header}";
            }
        }

        return $errors;
    }

    public static function validateRow(array $row, int $rowNumber): array
    {
        $errors = [];

        // Validate Order Number
        if (empty($row['Order Number'])) {
            $errors[] = "Row {$rowNumber}: Order Number is required";
        } elseif (strlen($row['Order Number']) > 255) {
            $errors[] = "Row {$rowNumber}: Order Number is too long (max 255 characters)";
        }

        // Validate Customer Email
        if (empty($row['Customer Email'])) {
            $errors[] = "Row {$rowNumber}: Customer Email is required";
        } elseif (!filter_var($row['Customer Email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Row {$rowNumber}: Invalid email format";
        }

        // Validate Customer Name
        if (empty($row['Customer Name'])) {
            $errors[] = "Row {$rowNumber}: Customer Name is required";
        } elseif (strlen($row['Customer Name']) > 255) {
            $errors[] = "Row {$rowNumber}: Customer Name is too long (max 255 characters)";
        }

        // Validate Status
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!empty($row['Status']) && !in_array(strtolower($row['Status']), $validStatuses)) {
            $errors[] = "Row {$rowNumber}: Invalid status. Must be one of: " . implode(', ', $validStatuses);
        }

        // Validate Payment Status
        $validPaymentStatuses = ['pending', 'paid', 'failed', 'refunded'];
        if (!empty($row['Payment Status']) && !in_array(strtolower($row['Payment Status']), $validPaymentStatuses)) {
            $errors[] = "Row {$rowNumber}: Invalid payment status. Must be one of: " . implode(', ', $validPaymentStatuses);
        }

        // Validate numeric fields
        $numericFields = ['Subtotal', 'Tax Amount', 'Shipping Amount', 'Total Amount'];
        foreach ($numericFields as $field) {
            if (!empty($row[$field]) && !is_numeric($row[$field])) {
                $errors[] = "Row {$rowNumber}: {$field} must be a valid number";
            }
        }

        return $errors;
    }

    public static function sanitizeRow(array $row): array
    {
        $sanitized = [];

        foreach ($row as $key => $value) {
            // Trim whitespace
            $value = trim($value);

            // Convert empty strings to null for optional fields
            if ($value === '') {
                $value = null;
            }

            // Sanitize specific fields
            switch ($key) {
                case 'Order Number':
                    $sanitized[$key] = strtoupper($value);
                    break;
                case 'Customer Email':
                    $sanitized[$key] = strtolower($value);
                    break;
                case 'Status':
                case 'Payment Status':
                    $sanitized[$key] = $value ? strtolower($value) : 'pending';
                    break;
                case 'Subtotal':
                case 'Tax Amount':
                case 'Shipping Amount':
                case 'Total Amount':
                    $sanitized[$key] = $value ? floatval($value) : 0;
                    break;
                default:
                    $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
