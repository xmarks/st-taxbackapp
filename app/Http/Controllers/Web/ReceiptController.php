<?php

namespace App\Http\Controllers\Web;

use App\Helpers\FlashMessageHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessReceiptScanRequest;
use App\Models\ScannedReceipt;
use App\Services\ReceiptScanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    public function __construct(
        private ReceiptScanService $receiptScanService
    ) {}

    public function scan(): View
    {
        return view('receipts.scan');
    }

    public function processScan(ProcessReceiptScanRequest $request): RedirectResponse
    {
        logger('Receipt scan attempt', [
            'user_id' => $request->user()->id,
            'qr_data_length' => strlen($request->validated('qr_data')),
            'qr_data_preview' => substr($request->validated('qr_data'), 0, 100)
        ]);

        $result = $this->receiptScanService->scanReceipt(
            $request->user(),
            $request->validated('qr_data')
        );

        logger('Receipt scan result', [
            'success' => $result['success'],
            'message' => $result['message'] ?? 'No message'
        ]);

        if (!$result['success']) {
            $errorMessage = FlashMessageHelper::receiptErrorMessage($result['message']);
            logger('Redirecting with error', ['error_message' => $errorMessage]);

            return redirect()->back()
                ->withInput()
                ->with('error', $errorMessage);
        }

        $receipt = $result['data']['receipt'];
        $successMessage = FlashMessageHelper::receiptScannedMessage($receipt);

        return redirect()->route('receipts.show', $receipt->id)
            ->with('success', $successMessage);
    }

    public function show(Request $request, ScannedReceipt $receipt): View
    {
        // Ensure user can only access their own receipts
        if ($receipt->user_id !== $request->user()->id) {
            abort(404);
        }

        $receipt->load(['seller', 'items', 'taxSummaries']);

        return view('receipts.show', [
            'receipt' => $receipt,
        ]);
    }

    public function index(Request $request): View
    {
        $receipts = $request->user()
            ->scannedReceipts()
            ->with(['seller'])
            ->orderBy('scanned_at', 'desc')
            ->paginate(15);

        return view('receipts.index', [
            'receipts' => $receipts,
        ]);
    }
}
