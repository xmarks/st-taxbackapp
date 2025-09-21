<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
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

    public function processScan(Request $request): RedirectResponse
    {
        $request->validate([
            'qr_data' => 'required|string',
        ]);

        $result = $this->receiptScanService->scanReceipt(
            $request->user(),
            $request->qr_data
        );

        if (!$result['success']) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['qr_data' => $result['message']]);
        }

        return redirect()->route('receipts.show', $result['data']['receipt']->id)
            ->with('success', 'Receipt scanned successfully! VAT amount: €' . number_format($result['data']['vat_amount'], 2));
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
