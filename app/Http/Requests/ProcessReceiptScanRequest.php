<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ProcessReceiptScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'qr_data' => [
                'required',
                'string',
                'min:20',
                'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'qr_data.required' => 'Please provide the QR code data or URL.',
            'qr_data.string' => 'The QR code data must be text.',
            'qr_data.min' => 'The QR code data appears to be too short to be valid.',
            'qr_data.max' => 'The QR code data is too long. Please check the format.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->hasValidQrDataFormat()) {
                $this->validateQrDataFormat($validator);
            }
        });
    }

    private function hasValidQrDataFormat(): bool
    {
        return !empty($this->qr_data);
    }

    private function validateQrDataFormat(Validator $validator): void
    {
        $qrData = $this->qr_data;

        // Check if it's a valid Albanian fiscal URL
        if (str_contains($qrData, 'efiskalizimi-app.tatime.gov.al')) {
            $this->validateFiscalUrl($validator, $qrData);
            return;
        }

        // Check if it's JSON format
        if (str_starts_with($qrData, '{')) {
            $this->validateJsonFormat($validator, $qrData);
            return;
        }

        // Check if it's query string format
        if (str_contains($qrData, 'iic=')) {
            $this->validateQueryStringFormat($validator, $qrData);
            return;
        }

        $validator->errors()->add('qr_data', 'Invalid QR code format. Expected Albanian fiscal URL, JSON data, or query parameters.');
    }

    private function validateFiscalUrl(Validator $validator, string $url): void
    {
        // Parse URL structure
        $urlParts = parse_url($url);

        if (!$urlParts) {
            $validator->errors()->add('qr_data', 'Invalid URL format.');
            return;
        }

        // Check for required fragment part
        if (!isset($urlParts['fragment']) || empty($urlParts['fragment'])) {
            $validator->errors()->add('qr_data', 'URL is missing required verification parameters.');
            return;
        }

        // Check for verify pattern
        if (!str_contains($urlParts['fragment'], '/verify?')) {
            $validator->errors()->add('qr_data', 'URL does not contain valid verification path.');
            return;
        }

        // Extract and validate parameters
        $queryPart = substr($urlParts['fragment'], strpos($urlParts['fragment'], '?') + 1);
        parse_str($queryPart, $params);

        // URL decode the parameters since they come from a URL
        $params = array_map('urldecode', $params);

        $this->validateRequiredParameters($validator, $params);
    }

    private function validateJsonFormat(Validator $validator, string $json): void
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $validator->errors()->add('qr_data', 'Invalid JSON format: ' . json_last_error_msg());
            return;
        }

        $this->validateRequiredParameters($validator, $data);
    }

    private function validateQueryStringFormat(Validator $validator, string $queryString): void
    {
        parse_str($queryString, $params);
        $this->validateRequiredParameters($validator, $params);
    }

    private function validateRequiredParameters(Validator $validator, array $params): void
    {
        $requiredParams = ['iic', 'tin', 'crtd', 'prc'];
        $missingParams = [];

        foreach ($requiredParams as $param) {
            if (!isset($params[$param]) || empty($params[$param])) {
                $missingParams[] = $param;
            }
        }

        if (!empty($missingParams)) {
            $validator->errors()->add('qr_data', 'Missing required parameters: ' . implode(', ', $missingParams));
            return;
        }

        // Validate parameter formats
        $this->validateParameterFormats($validator, $params);
    }

    private function validateParameterFormats(Validator $validator, array $params): void
    {
        // Validate IIC format (32 character hex string)
        if (!preg_match('/^[a-fA-F0-9]{32}$/', $params['iic'])) {
            $validator->errors()->add('qr_data', 'Invalid IIC format. Expected 32-character hexadecimal string.');
        }

        // Validate TIN format (Albanian tax number, should be 10 digits starting with K or L)
        if (!preg_match('/^[KL]\d{8}[A-Z]$/', $params['tin'])) {
            $validator->errors()->add('qr_data', 'Invalid TIN format. Expected Albanian tax identification number.');
        }

        // Validate date format (ISO 8601)
        if (!$this->isValidDateTime($params['crtd'])) {
            $validator->errors()->add('qr_data', 'Invalid date format. Expected ISO 8601 format. Received: "' . $params['crtd'] . '"');
        }

        // Validate price format (positive number)
        if (!is_numeric($params['prc']) || (float) $params['prc'] <= 0) {
            $validator->errors()->add('qr_data', 'Invalid price. Expected positive number.');
        }

        // Validate price range (reasonable limits)
        $price = (float) $params['prc'];
        if ($price > 1000000) {
            $validator->errors()->add('qr_data', 'Price seems unreasonably high. Please verify the receipt.');
        }
    }

    private function isValidDateTime(string $dateTime): bool
    {
        // Handle Albanian fiscal format specifically: "2025-09-13T18:56:07 02:00"
        // This format has a space and no + sign before timezone
        if (preg_match('/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}) (\d{2}:\d{2})$/', $dateTime, $matches)) {
            // Convert to standard ISO format by adding the + sign
            $standardFormat = $matches[1] . '+' . $matches[2];
            try {
                $date = new \DateTime($standardFormat);
                return true;
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
                $date = \DateTime::createFromFormat($format, $dateTime);
                if ($date !== false) {
                    return true;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Fallback to general DateTime parsing
        try {
            $date = new \DateTime($dateTime);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}