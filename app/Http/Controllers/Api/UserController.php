<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReceiptScanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(
        private ReceiptScanService $receiptScanService
    ) {}

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $this->receiptScanService->getUserReceiptStats($user);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'role_name' => $user->getRoleName(),
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                ],
                'stats' => [
                    'total_vat_amount' => $stats['total_vat_amount'],
                    'total_amount' => $stats['total_amount'],
                    'receipt_count' => $stats['receipt_count'],
                ],
            ],
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $request->user()->id,
        ]);

        $user = $request->user();

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
            $user->email_verified_at = null; // Reset email verification if email changes
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'role_name' => $user->getRoleName(),
                    'email_verified_at' => $user->email_verified_at,
                ],
            ],
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
            ], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Optionally revoke all tokens to force re-login
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully. Please login again.',
        ]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $this->receiptScanService->getUserReceiptStats($user);

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_vat_amount' => $stats['total_vat_amount'],
                    'total_amount' => $stats['total_amount'],
                    'receipt_count' => $stats['receipt_count'],
                ],
                'recent_receipts' => $stats['recent_receipts']->map(function ($receipt) {
                    return [
                        'id' => $receipt->id,
                        'iic' => $receipt->iic,
                        'total_price' => $receipt->total_price,
                        'total_vat_amount' => $receipt->total_vat_amount,
                        'scanned_at' => $receipt->scanned_at,
                        'seller' => [
                            'name' => $receipt->seller->name,
                            'town' => $receipt->seller->town,
                        ],
                    ];
                }),
                'monthly_stats' => $stats['monthly_stats'],
            ],
        ]);
    }
}
