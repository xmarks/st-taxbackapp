<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ReceiptException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessReceiptScanRequest;
use App\Models\ScannedReceipt;
use App\Services\AlbanianFiscalService;
use App\Services\ReceiptScanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReceiptController extends Controller
{
    public function __construct(
        private ReceiptScanService $receiptScanService
    ) {}

    public function scan(Request $request): JsonResponse
    {
        $request->validate([
            'qr_data' => 'required|string',
        ]);

        try {
            $result = $this->receiptScanService->scanReceipt(
                $request->user(),
                $request->qr_data
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'receipt' => [
                        'id' => $result['data']['receipt']->id,
                        'iic' => $result['data']['receipt']->iic,
                        'total_price' => $result['data']['receipt']->total_price,
                        'total_vat_amount' => $result['data']['receipt']->total_vat_amount,
                        'receipt_created_at' => $result['data']['receipt']->receipt_created_at,
                        'scanned_at' => $result['data']['receipt']->scanned_at,
                        'seller' => [
                            'name' => $result['data']['receipt']->seller->name,
                            'town' => $result['data']['receipt']->seller->town,
                            'country' => $result['data']['receipt']->seller->country,
                        ],
                    ],
                    'vat_amount' => $result['data']['vat_amount'],
                    'total_amount' => $result['data']['total_amount'],
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('Receipt scan API error', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while scanning the receipt',
            ], 500);
        }
    }

    public function index(Request $request): JsonResponse
    {
        $receipts = $request->user()
            ->scannedReceipts()
            ->with(['seller'])
            ->orderBy('scanned_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'receipts' => $receipts->items(),
                'pagination' => [
                    'current_page' => $receipts->currentPage(),
                    'last_page' => $receipts->lastPage(),
                    'per_page' => $receipts->perPage(),
                    'total' => $receipts->total(),
                ],
            ],
        ]);
    }

    public function show(Request $request, ScannedReceipt $receipt): JsonResponse
    {
        // Ensure user can only access their own receipts
        if ($receipt->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Receipt not found',
            ], 404);
        }

        $receipt->load(['seller', 'items', 'taxSummaries']);

        return response()->json([
            'success' => true,
            'data' => [
                'receipt' => $receipt,
            ],
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $stats = $this->receiptScanService->getUserReceiptStats($request->user());

        return response()->json([
            'success' => true,
            'data' => [
                'total_vat_amount' => $stats['total_vat_amount'],
                'total_amount' => $stats['total_amount'],
                'receipt_count' => $stats['receipt_count'],
                'recent_receipts' => $stats['recent_receipts']->map(function ($receipt) {
                    return [
                        'id' => $receipt->id,
                        'iic' => $receipt->iic,
                        'total_price' => $receipt->total_price,
                        'total_vat_amount' => $receipt->total_vat_amount,
                        'scanned_at' => $receipt->scanned_at,
                        'seller_name' => $receipt->seller->name,
                    ];
                }),
                'monthly_stats' => $stats['monthly_stats'],
            ],
        ]);
    }

    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'qr_data' => 'required|string',
        ]);

        try {
            // Use strict validation
            $fiscalService = app(AlbanianFiscalService::class);
            $qrData = $fiscalService->parseQrInputStrict($request->qr_data);

            // Check if receipt is expired
            $isExpired = $fiscalService->isReceiptExpired($qrData['dateTimeCreated']);

            if ($isExpired) {
                throw ReceiptException::expired($qrData['dateTimeCreated']);
            }

            // Check if already scanned
            $alreadyScanned = ScannedReceipt::existsByIic($qrData['iic']);

            return response()->json([
                'success' => true,
                'data' => [
                    'valid' => true,
                    'already_scanned' => $alreadyScanned,
                    'receipt_data' => [
                        'iic' => $qrData['iic'],
                        'price' => $qrData['price'],
                        'date_created' => $qrData['dateTimeCreated'],
                    ],
                ],
            ]);

        } catch (ReceiptException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getUserFriendlyMessage(),
                'error_code' => $e->getErrorCode(),
            ], $e->getStatusCode());

        } catch (\Exception $e) {
            Log::error('Receipt validation API error', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while validating the receipt',
            ], 500);
        }
    }
}
