<?php

namespace App\Http\Controllers\Web;

use App\Helpers\FlashMessageHelper;
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

        // Show welcome message for new users
        if ($stats['receipt_count'] === 0 && !session()->has('welcome_shown')) {
            $welcomeMessage = FlashMessageHelper::infoMessage('welcome', ['name' => $user->name]);
            session()->flash('info', $welcomeMessage);
            session(['welcome_shown' => true]);
        }

        // Show progress message if applicable
        $progressMessage = FlashMessageHelper::progressMessage($stats);
        if ($progressMessage) {
            session()->flash('info', $progressMessage);
        }

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
