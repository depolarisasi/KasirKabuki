<?php

namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    protected $userMessage;
    protected $errorCode;
    protected $statusCode;

    public function __construct(
        string $userMessage, 
        string $errorCode = 'BUSINESS_ERROR', 
        int $statusCode = 400,
        string $internalMessage = null,
        Exception $previous = null
    ) {
        $this->userMessage = $userMessage;
        $this->errorCode = $errorCode;
        $this->statusCode = $statusCode;
        
        // Use internal message for logging, user message for display
        $message = $internalMessage ?: $userMessage;
        
        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * Get user-friendly message
     */
    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    /**
     * Get error code
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Get HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Convert to array for JSON responses
     */
    public function toArray(): array
    {
        return [
            'success' => false,
            'message' => $this->userMessage,
            'error_code' => $this->errorCode,
            'status_code' => $this->statusCode,
        ];
    }

    // Static factory methods for common business exceptions

    public static function cartEmpty(): self
    {
        return new self(
            'Keranjang belanja kosong. Silakan tambahkan produk terlebih dahulu.',
            'CART_EMPTY',
            400
        );
    }

    public static function insufficientStock(string $productName, int $available = null): self
    {
        // If available stock is provided, include it in the message
        if ($available !== null) {
            $message = "Stok tidak mencukupi untuk {$productName}. Stok tersedia: {$available}";
        } else {
            $message = "Stok tidak mencukupi untuk {$productName}";
        }
        
        return new self(
            $message,
            'INSUFFICIENT_STOCK',
            400
        );
    }

    public static function productNotFound(string $productName = null): self
    {
        $message = $productName 
            ? "Produk '{$productName}' tidak ditemukan." 
            : 'Produk tidak ditemukan.';
            
        return new self($message, 'PRODUCT_NOT_FOUND', 404);
    }

    public static function orderNotFound(string $orderName = null): self
    {
        $message = $orderName 
            ? "Pesanan '{$orderName}' tidak ditemukan." 
            : 'Pesanan tidak ditemukan.';
            
        return new self($message, 'ORDER_NOT_FOUND', 404);
    }

    public static function transactionNotFound(string $transactionCode = null): self
    {
        $message = $transactionCode 
            ? "Transaksi '{$transactionCode}' tidak ditemukan." 
            : 'Transaksi tidak ditemukan.';
            
        return new self($message, 'TRANSACTION_NOT_FOUND', 404);
    }

    public static function invalidDiscount(string $reason = null): self
    {
        $message = $reason 
            ? "Diskon tidak valid: {$reason}" 
            : 'Diskon yang diterapkan tidak valid.';
            
        return new self($message, 'INVALID_DISCOUNT', 400);
    }

    public static function discountNotAllowed(string $reason = null): self
    {
        $message = $reason 
            ? "Diskon tidak dapat diterapkan: {$reason}" 
            : 'Diskon tidak dapat diterapkan pada transaksi ini.';
            
        return new self($message, 'DISCOUNT_NOT_ALLOWED', 400);
    }

    public static function orderAlreadyExists(string $orderName): self
    {
        return new self(
            "Nama pesanan '{$orderName}' sudah digunakan. Silakan gunakan nama lain.",
            'ORDER_NAME_EXISTS',
            409
        );
    }

    public static function invalidPaymentAmount(float $required, float $provided): self
    {
        $requiredFormatted = 'Rp ' . number_format($required, 0, ',', '.');
        $providedFormatted = 'Rp ' . number_format($provided, 0, ',', '.');
        
        return new self(
            "Jumlah pembayaran tidak mencukupi. Diperlukan: {$requiredFormatted}, Diberikan: {$providedFormatted}",
            'INSUFFICIENT_PAYMENT',
            400
        );
    }

    public static function transactionCannotBeCancelled(string $status): self
    {
        return new self(
            "Transaksi dengan status '{$status}' tidak dapat dibatalkan.",
            'TRANSACTION_CANNOT_BE_CANCELLED',
            400
        );
    }

    public static function unauthorizedAction(string $action = null): self
    {
        $message = $action 
            ? "Anda tidak memiliki akses untuk {$action}." 
            : 'Anda tidak memiliki akses untuk melakukan tindakan ini.';
            
        return new self($message, 'UNAUTHORIZED_ACTION', 403);
    }

    public static function stockInputAlreadyDone(string $productName, string $type = 'stok'): self
    {
        return new self(
            "Input {$type} untuk {$productName} hari ini sudah dilakukan.",
            'STOCK_INPUT_ALREADY_DONE',
            400
        );
    }

    public static function invalidStockAdjustment(string $reason): self
    {
        return new self(
            "Penyesuaian stok tidak valid: {$reason}",
            'INVALID_STOCK_ADJUSTMENT',
            400
        );
    }

    public static function partnerCommissionError(string $reason): self
    {
        return new self(
            "Error dalam kalkulasi komisi partner: {$reason}",
            'PARTNER_COMMISSION_ERROR',
            400
        );
    }
} 