<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Receipt Validity Hours
    |--------------------------------------------------------------------------
    |
    | Number of hours after receipt creation that it remains valid for scanning.
    | After this period, receipts cannot be scanned by users.
    |
    */
    'validity_hours' => env('RECEIPT_VALIDITY_HOURS', 24),

    /*
    |--------------------------------------------------------------------------
    | Albanian Fiscal System Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for Albanian fiscal system integration
    |
    */
    'fiscal_api' => [
        'base_url' => 'https://efiskalizimi-app.tatime.gov.al/invoice-check/api',
        'verify_endpoint' => '/verifyInvoice',
        'timeout' => 30, // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Rules for receipt validation
    |
    */
    'validation' => [
        'required_country' => 'ALB',
        'allow_duplicate_scans' => false,
        'allow_cross_user_duplicates' => false,
    ],
];