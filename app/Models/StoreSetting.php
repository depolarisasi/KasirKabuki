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
    ];

    protected $casts = [
        'show_receipt_logo' => 'boolean',
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
                'store_name' => 'Sate Braga',
                'store_address' => 'Jl. Braga No. 123, Bandung',
                'store_phone' => '022-1234567',
                'store_email' => 'info@satebraga.com',
                'receipt_header' => 'TERIMA KASIH ATAS KUNJUNGAN ANDA',
                'receipt_footer' => 'Selamat menikmati & sampai jumpa lagi!',
                'show_receipt_logo' => false,
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
} 