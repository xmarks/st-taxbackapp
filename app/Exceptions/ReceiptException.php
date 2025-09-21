<?php

namespace App\Exceptions;

use Exception;

class ReceiptException extends Exception
{
    public const ERROR_INVALID_FORMAT = 'INVALID_FORMAT';
    public const ERROR_EXPIRED = 'EXPIRED';
    public const ERROR_DUPLICATE = 'DUPLICATE';
    public const ERROR_NOT_ALBANIAN = 'NOT_ALBANIAN';
    public const ERROR_API_FAILED = 'API_FAILED';
    public const ERROR_NETWORK = 'NETWORK';
    public const ERROR_PARSING = 'PARSING';
    public const ERROR_UNAUTHORIZED = 'UNAUTHORIZED';

    private string $errorCode;
    private array $context;

    public function __construct(
        string $message,
        string $errorCode,
        array $context = [],
        int $code = 0,
        Exception $previous = null
    ) {
        $this->errorCode = $errorCode;
        $this->context = $context;

        parent::__construct($message, $code, $previous);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getUserFriendlyMessage(): string
    {
        return match ($this->errorCode) {
            self::ERROR_INVALID_FORMAT => 'The QR code format is invalid. Please scan a valid Albanian fiscal receipt.',
            self::ERROR_EXPIRED => 'This receipt has expired and cannot be scanned. Receipts must be scanned within ' . config('receipt.validity_hours', 24) . ' hours.',
            self::ERROR_DUPLICATE => 'This receipt has already been scanned.',
            self::ERROR_NOT_ALBANIAN => 'Only Albanian fiscal receipts can be processed.',
            self::ERROR_API_FAILED => 'Could not verify the receipt with the Albanian fiscal system. Please try again.',
            self::ERROR_NETWORK => 'Network error occurred while verifying the receipt. Please check your connection and try again.',
            self::ERROR_PARSING => 'Unable to parse the receipt data. Please ensure you have the complete QR code.',
            self::ERROR_UNAUTHORIZED => 'You are not authorized to perform this action.',
            default => $this->getMessage(),
        };
    }

    public function getStatusCode(): int
    {
        return match ($this->errorCode) {
            self::ERROR_UNAUTHORIZED => 403,
            self::ERROR_INVALID_FORMAT,
            self::ERROR_EXPIRED,
            self::ERROR_DUPLICATE,
            self::ERROR_NOT_ALBANIAN,
            self::ERROR_PARSING => 422,
            self::ERROR_API_FAILED,
            self::ERROR_NETWORK => 503,
            default => 400,
        };
    }

    public static function invalidFormat(string $details = ''): self
    {
        return new self(
            'Invalid QR code format' . ($details ? ": $details" : ''),
            self::ERROR_INVALID_FORMAT
        );
    }

    public static function expired(string $expiredAt = ''): self
    {
        return new self(
            'Receipt has expired' . ($expiredAt ? " (expired at: $expiredAt)" : ''),
            self::ERROR_EXPIRED,
            ['expired_at' => $expiredAt]
        );
    }

    public static function duplicate(string $iic = '', bool $userDuplicate = false): self
    {
        $message = $userDuplicate
            ? 'You have already scanned this receipt'
            : 'This receipt has already been scanned by another user';

        return new self(
            $message,
            self::ERROR_DUPLICATE,
            ['iic' => $iic, 'user_duplicate' => $userDuplicate]
        );
    }

    public static function notAlbanian(string $country = ''): self
    {
        return new self(
            'Receipt is not from Albania' . ($country ? " (found: $country)" : ''),
            self::ERROR_NOT_ALBANIAN,
            ['country' => $country]
        );
    }

    public static function apiFailed(string $reason = '', int $statusCode = 0): self
    {
        return new self(
            'Albanian fiscal API failed' . ($reason ? ": $reason" : ''),
            self::ERROR_API_FAILED,
            ['reason' => $reason, 'status_code' => $statusCode]
        );
    }

    public static function networkError(string $details = ''): self
    {
        return new self(
            'Network error occurred' . ($details ? ": $details" : ''),
            self::ERROR_NETWORK,
            ['details' => $details]
        );
    }

    public static function parsingError(string $details = ''): self
    {
        return new self(
            'Failed to parse receipt data' . ($details ? ": $details" : ''),
            self::ERROR_PARSING,
            ['details' => $details]
        );
    }

    public static function unauthorized(string $action = ''): self
    {
        return new self(
            'Unauthorized access' . ($action ? " for action: $action" : ''),
            self::ERROR_UNAUTHORIZED,
            ['action' => $action]
        );
    }
}