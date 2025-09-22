<?php

namespace App\Livewire;

use App\Helpers\FlashMessageHelper;
use App\Models\ScannedReceipt;
use App\Services\ReceiptScanService;
use Livewire\Component;

class Dashboard extends Component
{
    public $receiptToDelete = null;

    private $receiptScanService;

    public function confirmDeleteReceipt($receiptId)
    {
        $this->receiptToDelete = ScannedReceipt::where('id', $receiptId)
            ->where('user_id', auth()->id())
            ->with('seller')
            ->first();

        if (!$this->receiptToDelete) {
            session()->flash('error', 'Receipt not found or you do not have permission to delete it.');
            return;
        }

        $this->dispatch('open-delete-modal');
    }

    public function deleteReceipt()
    {
        if (!$this->receiptToDelete) {
            session()->flash('error', 'No receipt selected for deletion.');
            return;
        }

        // Ensure the user owns this receipt
        if ($this->receiptToDelete->user_id !== auth()->id()) {
            session()->flash('error', 'You do not have permission to delete this receipt.');
            return;
        }

        $receiptNumber = $this->receiptToDelete->invoice_order_number;
        $vatAmount = $this->receiptToDelete->total_vat_amount;

        // Delete the receipt
        $this->receiptToDelete->delete();

        // Clear the selected receipt
        $this->receiptToDelete = null;

        // Flash success message
        session()->flash('success', "Receipt #{$receiptNumber} has been permanently deleted. VAT amount of " . number_format($vatAmount, 2) . " ALL has been removed from your records.");
    }

    public function getStatsProperty()
    {
        $user = auth()->user();
        if (!$this->receiptScanService) {
            $this->receiptScanService = app(ReceiptScanService::class);
        }
        return $this->receiptScanService->getUserReceiptStats($user);
    }

    public function render()
    {
        $user = auth()->user();
        $stats = $this->stats;

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

        return view('livewire.dashboard', [
            'user' => $user,
            'stats' => $stats,
        ])->layout('layouts.app');
    }
}