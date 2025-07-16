<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    use HasFactory;

    protected $table = 'store_settings';

    protected $fillable = [
        'store_name',
        'store_address',
        'store_phone',
        'store_email',
        'receipt_header',
        'receipt_footer',
        'show_receipt_logo',
        'receipt_logo_path',
        'tax_rate',
        'service_charge_rate',
    ];

    protected $casts = [
        'show_receipt_logo' => 'boolean',
        'tax_rate' => 'decimal:2',
        'service_charge_rate' => 'decimal:2',
    ];

    /**
     * Get the store settings singleton.
     * Create default if not exists.
     */
    public static function current(): self
    {
        $settings = self::first();
        
        if (!$settings) {
            $settings = self::create([
                'store_name' => 'KasirKabuki',
                'store_address' => 'Jl. Braga No. 123, Bandung',
                'store_phone' => '022-1234567',
                'store_email' => 'info@kasirkabuki.com',
                'receipt_header' => 'TERIMA KASIH ATAS KUNJUNGAN ANDA',
                'receipt_footer' => 'Selamat menikmati & sampai jumpa lagi!',
                'show_receipt_logo' => false,
                'tax_rate' => 10.00,
                'service_charge_rate' => 5.00,
            ]);
        }
        
        return $settings;
    }

    /**
     * Update store settings.
     */
    public static function updateSettings(array $data): self
    {
        $settings = self::current();
        $settings->update($data);
        
        return $settings;
    }

    /**
     * Get formatted tax rate for display
     */
    public function getFormattedTaxRateAttribute(): string
    {
        return number_format($this->tax_rate, 1) . '%';
    }

    /**
     * Get formatted service charge rate for display
     */
    public function getFormattedServiceChargeRateAttribute(): string
    {
        return number_format($this->service_charge_rate, 1) . '%';
    }

    /**
     * Calculate tax amount from subtotal
     */
    public function calculateTaxAmount(float $subtotal): float
    {
        return round($subtotal * ($this->tax_rate / 100), 2);
    }

    /**
     * Calculate service charge amount from subtotal (including tax)
     */
    public function calculateServiceChargeAmount(float $subtotalWithTax): float
    {
        return round($subtotalWithTax * ($this->service_charge_rate / 100), 2);
    }

    /**
     * Calculate complete transaction breakdown
     * Order: Subtotal → Discount → Tax → Service Charge → Final Total
     */
    public function calculateTransactionBreakdown(float $subtotal, float $discount = 0): array
    {
        $afterDiscount = $subtotal - $discount;
        $taxAmount = $this->calculateTaxAmount($afterDiscount);
        $subtotalWithTax = $afterDiscount + $taxAmount;
        $serviceChargeAmount = $this->calculateServiceChargeAmount($subtotalWithTax);
        $finalTotal = $subtotalWithTax + $serviceChargeAmount;

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'after_discount' => round($afterDiscount, 2),
            'tax_amount' => round($taxAmount, 2),
            'tax_rate' => $this->tax_rate,
            'subtotal_with_tax' => round($subtotalWithTax, 2),
            'service_charge_amount' => round($serviceChargeAmount, 2),
            'service_charge_rate' => $this->service_charge_rate,
            'final_total' => round($finalTotal, 2),
        ];
    }

    /**
     * Validate tax rate (0-100%)
     */
    public static function validateTaxRate($value): bool
    {
        return is_numeric($value) && $value >= 0 && $value <= 100;
    }

    /**
     * Validate service charge rate (0-100%)
     */
    public static function validateServiceChargeRate($value): bool
    {
        return is_numeric($value) && $value >= 0 && $value <= 100;
    }
} 