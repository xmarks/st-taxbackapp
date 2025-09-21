<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\AlbanianFiscalService;

$service = new AlbanianFiscalService();

echo "Config validity hours: " . config('receipt.validity_hours') . "\n";
echo "Receipt from 2025-09-13 expired: " . ($service->isReceiptExpired('2025-09-13T18:56:07+02:00') ? 'YES' : 'NO') . "\n";

// Delete this file after use
unlink(__FILE__);