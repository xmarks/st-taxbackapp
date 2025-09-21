<?php

namespace App\Services;

use App\Models\ScannedReceipt;
use App\Models\Seller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReceiptScanService
{
    public function __construct(
        private AlbanianFiscalService $fiscalService
    ) {}

    public function scanReceipt(User $user, string $qrUrlOrData): array
    {
        try {
            DB::beginTransaction();

            // Step 1: Parse QR URL or extract data
            $qrData = $this->parseQrInput($qrUrlOrData);
            if (!$qrData) {
                return $this->errorResponse('Invalid QR code format');
            }

            // Step 2: Check if receipt is expired (before API call)
            if ($this->fiscalService->isReceiptExpired($qrData['dateTimeCreated'])) {
                return $this->errorResponse('Receipt has expired and cannot be scanned');
            }

            // Step 3: Check for duplicates
            $duplicateCheck = $this->checkForDuplicates($user, $qrData['iic']);
            if (!$duplicateCheck['allowed']) {
                return $this->errorResponse($duplicateCheck['message']);
            }

            // Step 4: Call Albanian Fiscal API
            $apiResult = $this->fiscalService->verifyInvoice(
                $qrData['iic'],
                $qrData['tin'],
                $qrData['dateTimeCreated'],
                $qrData['price']
            );

            if (!$apiResult['success']) {
                return $this->errorResponse($apiResult['error']);
            }

            $apiData = $apiResult['data'];

            // Step 5: Create or update seller
            $sellerData = $this->fiscalService->extractSellerData($apiData);
            $seller = Seller::findOrCreateBySeller($sellerData);

            // Step 6: Create scanned receipt record
            $receiptData = $this->fiscalService->extractReceiptData($apiData);
            $receiptData['user_id'] = $user->id;
            $receiptData['seller_id'] = $seller->id;
            $receiptData['iic'] = $qrData['iic'];
            $receiptData['scanned_at'] = Carbon::now();
            $receiptData['original_url'] = $qrUrlOrData;
            $receiptData['url_price'] = $qrData['price'];
            $receiptData['raw_api_response'] = $apiData;

            $scannedReceipt = ScannedReceipt::create($receiptData);

            // Step 7: Create receipt items
            $itemsData = $this->fiscalService->extractItemsData($apiData);
            foreach ($itemsData as $itemData) {
                $itemData['scanned_receipt_id'] = $scannedReceipt->id;
                $scannedReceipt->items()->create($itemData);
            }

            // Step 8: Create tax summaries
            $taxSummariesData = $this->fiscalService->extractTaxSummariesData($apiData);
            foreach ($taxSummariesData as $taxData) {
                $taxData['scanned_receipt_id'] = $scannedReceipt->id;
                $scannedReceipt->taxSummaries()->create($taxData);
            }

            DB::commit();

            Log::info('Receipt scanned successfully', [
                'user_id' => $user->id,
                'iic' => $qrData['iic'],
                'total_vat' => $scannedReceipt->total_vat_amount,
            ]);

            return [
                'success' => true,
                'message' => 'Receipt scanned successfully',
                'data' => [
                    'receipt' => $scannedReceipt->load(['seller', 'items', 'taxSummaries']),
                    'vat_amount' => $scannedReceipt->total_vat_amount,
                    'total_amount' => $scannedReceipt->total_price,
                ],
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Receipt scanning failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('An error occurred while processing the receipt');
        }
    }

    private function parseQrInput(string $input): ?array
    {
        // If it's a URL, parse it
        if (str_contains($input, 'efiskalizimi-app.tatime.gov.al')) {
            return $this->fiscalService->parseQrUrl($input);
        }

        // If it's already JSON data, decode it
        if (str_starts_with($input, '{')) {
            $data = json_decode($input, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $data;
            }
        }

        return null;
    }

    private function checkForDuplicates(User $user, string $iic): array
    {
        // Check if this specific user has already scanned this receipt
        if (ScannedReceipt::userAlreadyScanned($user->id, $iic)) {
            return [
                'allowed' => false,
                'message' => 'You have already scanned this receipt',
            ];
        }

        // Check if any user has already scanned this receipt (global duplicate check)
        if (ScannedReceipt::existsByIic($iic)) {
            return [
                'allowed' => false,
                'message' => 'This receipt has already been scanned by another user',
            ];
        }

        return ['allowed' => true];
    }

    private function errorResponse(string $message): array
    {
        return [
            'success' => false,
            'message' => $message,
            'data' => null,
        ];
    }

    public function getUserReceiptStats(User $user): array
    {
        $receipts = $user->scannedReceipts()
            ->with(['seller'])
            ->orderBy('scanned_at', 'desc')
            ->get();

        $totalVat = $receipts->sum('total_vat_amount');
        $totalAmount = $receipts->sum('total_price');
        $receiptCount = $receipts->count();

        $monthlyStats = $receipts->groupBy(function ($receipt) {
            return $receipt->scanned_at->format('Y-m');
        })->map(function ($monthReceipts) {
            return [
                'count' => $monthReceipts->count(),
                'total_vat' => $monthReceipts->sum('total_vat_amount'),
                'total_amount' => $monthReceipts->sum('total_price'),
            ];
        });

        return [
            'total_vat_amount' => $totalVat,
            'total_amount' => $totalAmount,
            'receipt_count' => $receiptCount,
            'recent_receipts' => $receipts->take(10),
            'monthly_stats' => $monthlyStats,
        ];
    }
}