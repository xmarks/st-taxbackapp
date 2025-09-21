<?php

namespace App\Http\Middleware;

use App\Services\ReceiptScanService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FlashMilestoneMessages
{
    public function __construct(
        private ReceiptScanService $receiptScanService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only run for authenticated users on successful responses
        if (!$request->user() || !$response->isSuccessful()) {
            return $response;
        }

        // Only check on dashboard and receipt pages
        if (!$request->routeIs(['dashboard', 'receipts.*'])) {
            return $response;
        }

        $this->checkMilestones($request);

        return $response;
    }

    private function checkMilestones(Request $request): void
    {
        $user = $request->user();
        $stats = $this->receiptScanService->getUserReceiptStats($user);

        $vatAmount = $stats['total_vat_amount'];
        $receiptCount = $stats['receipt_count'];

        // VAT amount milestones
        $vatMilestones = [
            50 => '🎉 Congratulations! You\'ve collected over 50 ALL in VAT! You\'re well on your way to a nice refund.',
            100 => '🚀 Amazing! 100+ ALL in VAT collected! Your diligent receipt scanning is paying off.',
            250 => '💰 Incredible! You\'ve reached 250+ ALL in VAT! That\'s a substantial refund coming your way.',
            500 => '🏆 Outstanding! 500+ ALL in VAT tracked! You\'re a receipt scanning champion!',
            1000 => '👑 Legendary! 1000+ ALL in VAT! You\'ve mastered the art of VAT tracking!'
        ];

        // Receipt count milestones
        $countMilestones = [
            5 => '📋 Nice work! You\'ve scanned 5 receipts. Building a solid VAT tracking habit!',
            10 => '🔟 Double digits! 10 receipts scanned. You\'re getting the hang of this!',
            25 => '📊 Quarter century! 25 receipts tracked. Your organization skills are impressive!',
            50 => '📈 Half a hundred! 50 receipts in your collection. VAT tracking expert level!',
            100 => '💯 Century milestone! 100 receipts scanned. You\'re a true professional!'
        ];

        $this->checkAndFlashMilestone($vatMilestones, $vatAmount, 'vat_milestone_');
        $this->checkAndFlashMilestone($countMilestones, $receiptCount, 'count_milestone_');
    }

    private function checkAndFlashMilestone(array $milestones, float $value, string $sessionPrefix): void
    {
        foreach ($milestones as $threshold => $message) {
            $sessionKey = $sessionPrefix . $threshold;

            // If user has reached this milestone and hasn't seen the message yet
            if ($value >= $threshold && !session()->has($sessionKey)) {
                session()->flash('success', $message);
                session([$sessionKey => true]);
                break; // Only show one milestone message at a time
            }
        }
    }
}