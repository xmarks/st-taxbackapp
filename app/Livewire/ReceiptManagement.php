<?php

namespace App\Livewire;

use App\Models\ScannedReceipt;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class ReceiptManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $minAmount = '';
    public $maxAmount = '';
    public $minVat = '';
    public $maxVat = '';
    public $sortBy = 'scanned_at';
    public $sortDirection = 'desc';
    public $receiptToDelete = null;

    protected $queryString = ['search', 'dateFrom', 'dateTo', 'minAmount', 'maxAmount', 'minVat', 'maxVat', 'sortBy', 'sortDirection'];

    public function mount()
    {
        // Set default date range to last 30 days if not specified
        if (empty($this->dateFrom) && empty($this->dateTo)) {
            $this->dateTo = Carbon::today()->format('Y-m-d');
            $this->dateFrom = Carbon::today()->subDays(30)->format('Y-m-d');
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function updatedMinAmount()
    {
        $this->resetPage();
    }

    public function updatedMaxAmount()
    {
        $this->resetPage();
    }

    public function updatedMinVat()
    {
        $this->resetPage();
    }

    public function updatedMaxVat()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->minAmount = '';
        $this->maxAmount = '';
        $this->minVat = '';
        $this->maxVat = '';
        $this->resetPage();
    }

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

        // Reset pagination to avoid empty pages
        $this->resetPage();
    }

    public function getReceiptsProperty()
    {
        $query = auth()->user()
            ->scannedReceipts()
            ->with(['seller']);

        // Search by seller name, invoice number, or IIC
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('invoice_order_number', 'like', '%' . $this->search . '%')
                  ->orWhere('iic', 'like', '%' . $this->search . '%')
                  ->orWhereHas('seller', function ($sellerQuery) {
                      $sellerQuery->where('name', 'like', '%' . $this->search . '%')
                                  ->orWhere('town', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Date range filter
        if (!empty($this->dateFrom)) {
            $query->whereDate('scanned_at', '>=', $this->dateFrom);
        }
        if (!empty($this->dateTo)) {
            $query->whereDate('scanned_at', '<=', $this->dateTo);
        }

        // Amount range filter
        if (!empty($this->minAmount)) {
            $query->where('total_price', '>=', $this->minAmount);
        }
        if (!empty($this->maxAmount)) {
            $query->where('total_price', '<=', $this->maxAmount);
        }

        // VAT range filter
        if (!empty($this->minVat)) {
            $query->where('total_vat_amount', '>=', $this->minVat);
        }
        if (!empty($this->maxVat)) {
            $query->where('total_vat_amount', '<=', $this->maxVat);
        }

        // Sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate(15);
    }

    public function getStatsProperty()
    {
        $receipts = $this->receipts;

        return [
            'total_count' => $receipts->total(),
            'total_vat' => $receipts->sum('total_vat_amount'),
            'total_amount' => $receipts->sum('total_price'),
            'current_page_count' => $receipts->count(),
        ];
    }

    public function render()
    {
        return view('livewire.receipt-management', [
            'receipts' => $this->receipts,
            'stats' => $this->stats,
        ])->layout('layouts.app');
    }
}
