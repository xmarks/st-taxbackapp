<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ReceiptScanService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private ReceiptScanService $receiptScanService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $stats = $this->receiptScanService->getUserReceiptStats($user);

        return view('dashboard', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }

    public function receipts(Request $request): View
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
