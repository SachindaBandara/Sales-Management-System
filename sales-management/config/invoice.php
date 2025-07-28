<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Invoice Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for invoice generation.
    | You can modify these values to match your company information.
    |
    */

    'company' => [
        'name' => env('INVOICE_COMPANY_NAME', 'Your Company Name'),
        'address' => env('INVOICE_COMPANY_ADDRESS', '123 Business Street'),
        'city' => env('INVOICE_COMPANY_CITY', 'Kalutara'),
        'postal_code' => env('INVOICE_COMPANY_POSTAL_CODE', '12000'),
        'country' => env('INVOICE_COMPANY_COUNTRY', 'Sri Lanka'),
        'phone' => env('INVOICE_COMPANY_PHONE', '+94 XX XXX XXXX'),
        'email' => env('INVOICE_COMPANY_EMAIL', 'info@company.com'),
        'website' => env('INVOICE_COMPANY_WEBSITE', 'www.company.com'),
        'tax_number' => env('INVOICE_COMPANY_TAX_NUMBER', 'TAX123456'),
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for PDF generation using dompdf
    |
    */
    'pdf' => [
        'paper' => 'a4',
        'orientation' => 'portrait',
        'dpi' => 150,
        'font' => 'sans-serif',
        'options' => [
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'isRemoteEnabled' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoice Settings
    |--------------------------------------------------------------------------
    |
    | General settings for invoice generation
    |
    */
    'settings' => [
        'invoice_prefix' => env('INVOICE_PREFIX', 'INV'),
        'due_days' => env('INVOICE_DUE_DAYS', 30),
        'currency_symbol' => env('INVOICE_CURRENCY_SYMBOL', 'LKR'),
        'include_tax_details' => env('INVOICE_INCLUDE_TAX_DETAILS', true),
        'include_shipping_details' => env('INVOICE_INCLUDE_SHIPPING_DETAILS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Storage
    |--------------------------------------------------------------------------
    |
    | Configuration for temporary file storage during bulk operations
    |
    */
    'storage' => [
        'temp_path' => storage_path('app/temp'),
        'cleanup_after_hours' => 24,
    ],
];
