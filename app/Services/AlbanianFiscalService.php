<?php

namespace App\Services;

use App\Exceptions\ReceiptException;
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

        // URL decode the parameters since they come from a URL
        $params = array_map('urldecode', $params);

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

    /**
     * Parse QR input with strict validation that throws exceptions
     */
    public function parseQrInputStrict(string $input): array
    {
        // If it's a URL, parse it
        if (str_contains($input, 'efiskalizimi-app.tatime.gov.al')) {
            $data = $this->parseQrUrl($input);
            if (!$data) {
                throw ReceiptException::invalidFormat('Invalid Albanian fiscal URL format');
            }
            return $data;
        }

        // If it's JSON data, decode it
        if (str_starts_with($input, '{')) {
            $data = json_decode($input, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw ReceiptException::parsingError('Invalid JSON: ' . json_last_error_msg());
            }

            $this->validateQrParameters($data);
            return $data;
        }

        // If it's query string format
        if (str_contains($input, 'iic=')) {
            parse_str($input, $data);
            $this->validateQrParameters($data);
            return $data;
        }

        throw ReceiptException::invalidFormat('Unrecognized QR code format');
    }

    /**
     * Validate QR parameters with detailed error messages
     */
    private function validateQrParameters(array $params): void
    {
        $requiredParams = ['iic', 'tin', 'crtd', 'prc'];
        $missing = [];

        foreach ($requiredParams as $param) {
            if (!isset($params[$param]) || empty($params[$param])) {
                $missing[] = $param;
            }
        }

        if (!empty($missing)) {
            throw ReceiptException::invalidFormat('Missing required parameters: ' . implode(', ', $missing));
        }

        // Validate IIC format
        if (!preg_match('/^[a-fA-F0-9]{32}$/', $params['iic'])) {
            throw ReceiptException::invalidFormat('Invalid IIC format');
        }

        // Validate TIN format
        if (!preg_match('/^[KL]\d{8}[A-Z]$/', $params['tin'])) {
            throw ReceiptException::invalidFormat('Invalid Albanian TIN format');
        }

        // Validate price
        if (!is_numeric($params['prc']) || (float) $params['prc'] <= 0) {
            throw ReceiptException::invalidFormat('Invalid price value');
        }

        // Validate date
        try {
            $this->parseAlbanianDateTime($params['crtd']);
        } catch (\Exception $e) {
            throw ReceiptException::invalidFormat('Invalid date format');
        }
    }

    public function isValidAlbanianReceipt(array $data): bool
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
            $receiptDate = $this->parseAlbanianDateTime($dateTimeCreated);
            $validityHours = (int) config('receipt.validity_hours', 24);
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

    /**
     * Parse Albanian fiscal system date format with fallback to other formats
     */
    private function parseAlbanianDateTime(string $dateTime): Carbon
    {
        // Handle Albanian fiscal format specifically: "2025-09-13T18:56:07 02:00"
        // This format has a space and no + sign before timezone
        if (preg_match('/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}) (\d{2}:\d{2})$/', $dateTime, $matches)) {
            // Convert to standard ISO format by adding the + sign
            $standardFormat = $matches[1] . '+' . $matches[2];
            try {
                return new Carbon($standardFormat);
            } catch (\Exception $e) {
                // Continue to other formats
            }
        }

        // List of other acceptable date formats
        $formats = [
            \DateTime::ATOM,                    // 2025-09-13T18:56:07+02:00
            'Y-m-d\TH:i:s P',                  // 2025-09-13T18:56:07 +02:00 (with space and +)
            'Y-m-d\TH:i:s.u P',                // 2025-09-13T18:56:07.000 +02:00
            'Y-m-d\TH:i:s.uP',                 // 2025-09-13T18:56:07.000+02:00
            'Y-m-d\TH:i:sP',                   // 2025-09-13T18:56:07+02:00
            'Y-m-d\TH:i:s O',                  // 2025-09-13T18:56:07 +0200
            'Y-m-d\TH:i:sO',                   // 2025-09-13T18:56:07+0200
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $dateTime);
                if ($date !== false) {
                    return $date;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Fallback to general DateTime parsing
        return new Carbon($dateTime);
    }

    public function extractReceiptData(array $apiResponse): array
    {
        return [
            'fiscal_id' => $apiResponse['id'] ?? $apiResponse['fic'] ?? $apiResponse['iic'] ?? 'unknown',
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
            'receipt_created_at' => $this->parseAlbanianDateTime($apiResponse['dateTimeCreated']),
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
                'item_fiscal_id' => $item['id'] ?? 'item_' . $item['code'] ?? 'unknown',
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
                    'tax_fiscal_id' => $tax['id'] ?? 'tax_' . $tax['vatRate'] ?? 'unknown',
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