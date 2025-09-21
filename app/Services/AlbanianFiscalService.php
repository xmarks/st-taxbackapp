<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AlbanianFiscalService
{
    private const BASE_URL = 'https://efiskalizimi-app.tatime.gov.al/invoice-check/api';
    private const VERIFY_ENDPOINT = '/verifyInvoice';

    public function verifyInvoice(string $iic, string $tin, string $dateTimeCreated, float $price): array
    {
        try {
            $response = Http::asForm()
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8',
                ])
                ->post(self::BASE_URL . self::VERIFY_ENDPOINT, [
                    'iic' => $iic,
                    'tin' => $tin,
                    'dateTimeCreated' => $dateTimeCreated,
                    'prc' => $price,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                // Validate that this is from Albania
                if (!$this->isValidAlbanianReceipt($data)) {
                    return [
                        'success' => false,
                        'error' => 'Receipt is not from Albania or invalid location data',
                        'data' => null,
                    ];
                }

                return [
                    'success' => true,
                    'error' => null,
                    'data' => $data,
                ];
            }

            Log::error('Albanian Fiscal API error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'iic' => $iic,
            ]);

            return [
                'success' => false,
                'error' => 'Failed to verify receipt with fiscal system',
                'data' => null,
            ];

        } catch (\Exception $e) {
            Log::error('Albanian Fiscal API exception', [
                'message' => $e->getMessage(),
                'iic' => $iic,
            ]);

            return [
                'success' => false,
                'error' => 'Error connecting to fiscal system: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    public function parseQrUrl(string $url): ?array
    {
        // Parse the QR code URL to extract parameters
        $urlParts = parse_url($url);

        if (!$urlParts || !isset($urlParts['fragment'])) {
            return null;
        }

        // Extract fragment part (everything after #)
        $fragment = $urlParts['fragment'];

        // Parse the verify part
        if (!str_contains($fragment, '/verify?')) {
            return null;
        }

        $queryPart = substr($fragment, strpos($fragment, '?') + 1);
        parse_str($queryPart, $params);

        // Validate required parameters
        $requiredParams = ['iic', 'tin', 'crtd', 'prc'];
        foreach ($requiredParams as $param) {
            if (!isset($params[$param])) {
                return null;
            }
        }

        return [
            'iic' => $params['iic'],
            'tin' => $params['tin'],
            'dateTimeCreated' => $params['crtd'],
            'price' => (float) $params['prc'],
        ];
    }

    private function isValidAlbanianReceipt(array $data): bool
    {
        // Check if seller information exists and is from Albania
        if (!isset($data['seller'])) {
            return false;
        }

        $seller = $data['seller'];

        // Validate country
        if (!isset($seller['country']) || $seller['country'] !== 'ALB') {
            return false;
        }

        // Validate that town exists and is not empty
        if (!isset($seller['town']) || empty($seller['town'])) {
            return false;
        }

        return true;
    }

    public function isReceiptExpired(string $dateTimeCreated): bool
    {
        try {
            $receiptDate = Carbon::parse($dateTimeCreated);
            $validityHours = config('receipt.validity_hours', 24);
            $expiryTime = $receiptDate->addHours($validityHours);

            return Carbon::now()->gt($expiryTime);
        } catch (\Exception $e) {
            Log::error('Error parsing receipt date', [
                'dateTimeCreated' => $dateTimeCreated,
                'error' => $e->getMessage(),
            ]);
            return true; // Consider expired if we can't parse the date
        }
    }

    public function extractReceiptData(array $apiResponse): array
    {
        return [
            'fiscal_id' => $apiResponse['id'],
            'total_price' => $apiResponse['totalPrice'],
            'total_price_without_vat' => $apiResponse['totalPriceWithoutVAT'],
            'total_vat_amount' => $apiResponse['totalVATAmount'],
            'fic' => $apiResponse['fic'],
            'invoice_number' => $apiResponse['invoiceNumber'],
            'invoice_order_number' => $apiResponse['invoiceOrderNumber'],
            'business_unit' => $apiResponse['businessUnit'],
            'cash_register' => $apiResponse['cashRegister'],
            'tcr_code' => $apiResponse['tcrCode'],
            'operator_code' => $apiResponse['operatorCode'] ?? null,
            'software_code' => $apiResponse['softwareCode'] ?? null,
            'receipt_created_at' => Carbon::parse($apiResponse['dateTimeCreated']),
            'tax_free_amount' => $apiResponse['taxFreeAmt'] ?? 0,
            'invoice_type' => $apiResponse['invoiceType'],
            'invoice_version' => $apiResponse['invoiceVersion'],
            'is_einvoice' => $apiResponse['isEinvoice'],
            'simplified_invoice' => $apiResponse['simplifiedInvoice'],
        ];
    }

    public function extractSellerData(array $apiResponse): array
    {
        $seller = $apiResponse['seller'];

        return [
            'idNum' => $seller['idNum'],
            'idType' => $seller['idType'],
            'name' => $seller['name'],
            'address' => $seller['address'] ?? null,
            'town' => $seller['town'],
            'country' => $seller['country'],
        ];
    }

    public function extractItemsData(array $apiResponse): array
    {
        $items = [];

        foreach ($apiResponse['items'] as $item) {
            $items[] = [
                'item_fiscal_id' => $item['id'],
                'name' => $item['name'],
                'code' => $item['code'],
                'unit' => $item['unit'],
                'quantity' => $item['quantity'],
                'unit_price_before_vat' => $item['unitPriceBeforeVat'],
                'unit_price_after_vat' => $item['unitPriceAfterVat'],
                'price_before_vat' => $item['priceBeforeVat'],
                'price_after_vat' => $item['priceAfterVat'],
                'vat_rate' => $item['vatRate'],
                'vat_amount' => $item['vatAmount'],
                'exempt_from_vat' => $item['exemptFromVat'] ?? false,
                'rebate' => $item['rebate'] ?? 0,
                'rebate_reducing' => $item['rebateReducing'] ?? true,
                'investment' => $item['investment'] ?? false,
            ];
        }

        return $items;
    }

    public function extractTaxSummariesData(array $apiResponse): array
    {
        $summaries = [];

        if (isset($apiResponse['sameTaxes'])) {
            foreach ($apiResponse['sameTaxes'] as $tax) {
                $summaries[] = [
                    'tax_fiscal_id' => $tax['id'],
                    'number_of_items' => $tax['numberOfItems'],
                    'price_before_vat' => $tax['priceBeforeVat'],
                    'vat_rate' => $tax['vatRate'],
                    'vat_amount' => $tax['vatAmount'],
                    'exempt_from_vat' => $tax['exemptFromVat'] ?? false,
                ];
            }
        }

        return $summaries;
    }
}