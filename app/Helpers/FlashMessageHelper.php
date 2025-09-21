<?php

namespace App\Helpers;

use App\Models\ScannedReceipt;

class FlashMessageHelper
{
    /**
     * Generate a contextual success message for a scanned receipt
     */
    public static function receiptScannedMessage(ScannedReceipt $receipt): string
    {
        $vatAmount = $receipt->total_vat_amount;
        $sellerName = $receipt->seller->name;
        $totalAmount = $receipt->total_price;

        // Different messages based on VAT amount
        if ($vatAmount >= 50) {
            $emoji = '🚀';
            $descriptor = 'Excellent';
        } elseif ($vatAmount >= 20) {
            $emoji = '💰';
            $descriptor = 'Great';
        } elseif ($vatAmount >= 10) {
            $emoji = '✨';
            $descriptor = 'Nice';
        } else {
            $emoji = '✅';
            $descriptor = 'Good';
        }

        return sprintf(
            '%s %s work! Receipt from %s scanned successfully. Added %s ALL VAT (Total: %s ALL)',
            $emoji,
            $descriptor,
            $sellerName,
            number_format($vatAmount, 2),
            number_format($totalAmount, 2)
        );
    }

    /**
     * Generate error message with helpful context
     */
    public static function receiptErrorMessage(string $error, string $context = ''): string
    {
        $helpfulTips = [
            'expired' => 'Remember: Albanian receipts must be scanned within ' . config('receipt.validity_hours', 24) . ' hours of creation.',
            'duplicate' => 'Each receipt can only be scanned once to prevent fraud.',
            'invalid' => 'Make sure you\'re scanning a complete QR code from an Albanian fiscal receipt.',
            'network' => 'Please check your internet connection and try again.',
            'albania' => 'Only receipts from Albanian businesses can be processed for VAT refunds.',
        ];

        $tip = '';
        foreach ($helpfulTips as $keyword => $helpText) {
            if (str_contains(strtolower($error), $keyword)) {
                $tip = ' ' . $helpText;
                break;
            }
        }

        return $error . $tip . ($context ? " ($context)" : '');
    }

    /**
     * Generate progress messages for user stats
     */
    public static function progressMessage(array $stats): ?string
    {
        $receiptCount = $stats['receipt_count'];
        $vatAmount = $stats['total_vat_amount'];

        // Motivational messages based on progress
        if ($receiptCount === 0) {
            return null; // No progress to show
        }

        if ($receiptCount === 1) {
            return sprintf(
                '🎯 First receipt scanned! You\'ve started with %s ALL in VAT. Keep scanning to build your refund!',
                number_format($vatAmount, 2)
            );
        }

        if ($receiptCount % 10 === 0) {
            return sprintf(
                '📊 You\'ve scanned %d receipts and collected %s ALL in VAT! You\'re building an impressive collection.',
                $receiptCount,
                number_format($vatAmount, 2)
            );
        }

        // Monthly achievement
        $thisMonth = $stats['monthly_stats'][date('Y-m')] ?? null;
        if ($thisMonth && $thisMonth['count'] === 5) {
            return sprintf(
                '📅 5 receipts this month! You\'ve added %s ALL in VAT this month alone.',
                number_format($thisMonth['total_vat'], 2)
            );
        }

        return null;
    }

    /**
     * Generate warning messages for potential issues
     */
    public static function warningMessage(string $type, array $context = []): string
    {
        return match ($type) {
            'approaching_limit' => sprintf(
                '⚠️ You\'re approaching the daily scanning limit. You have %d scans remaining today.',
                $context['remaining'] ?? 0
            ),
            'old_receipt' => sprintf(
                '⏰ This receipt is %d hours old. Remember, receipts expire after ' . config('receipt.validity_hours', 24) . ' hours.',
                $context['hours'] ?? 0
            ),
            'low_vat' => sprintf(
                '💡 This receipt has only %s ALL in VAT. Look for receipts with higher amounts for better refunds!',
                number_format($context['vat_amount'] ?? 0, 2)
            ),
            'network_slow' => '🐌 Network connection seems slow. Receipt processing might take longer than usual.',
            default => 'Please check the information and try again.'
        };
    }

    /**
     * Generate info messages for tips and guidance
     */
    public static function infoMessage(string $type, array $context = []): string
    {
        return match ($type) {
            'welcome' => sprintf(
                '👋 Welcome %s! Scan Albanian receipts to track VAT amounts for your tax refund.',
                $context['name'] ?? 'to Tax Back App'
            ),
            'first_scan_tip' => '💡 Tip: Look for the QR code on the bottom of Albanian receipts. It contains all the data needed for VAT tracking.',
            'scan_more' => sprintf(
                '🎯 You have %s ALL in VAT so far. Scan more receipts to increase your potential refund!',
                number_format($context['current_vat'] ?? 0, 2)
            ),
            'monthly_goal' => sprintf(
                '📈 Goal: Scan %d more receipts this month to reach your target of %d receipts!',
                $context['needed'] ?? 5,
                $context['goal'] ?? 10
            ),
            default => 'Continue scanning receipts to build your VAT collection!'
        };
    }
}