<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ProductPartnerPrice;

class Product extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'photo',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the partner prices for this product.
     */
    public function partnerPrices(): HasMany
    {
        return $this->hasMany(ProductPartnerPrice::class);
    }

    /**
     * Get active partner prices for this product.
     */
    public function activePartnerPrices(): HasMany
    {
        return $this->partnerPrices()->where('is_active', true);
    }

    /**
     * Get product components jika ini adalah package product
     */
    public function components(): HasMany
    {
        return $this->hasMany(ProductComponent::class, 'package_product_id');
    }

    /**
     * Get active product components
     */
    public function activeComponents(): HasMany
    {
        return $this->components()->where('is_active', true);
    }

    /**
     * Get packages yang menggunakan product ini sebagai component
     */
    public function packagesUsingThisComponent(): HasMany
    {
        return $this->hasMany(ProductComponent::class, 'component_product_id');
    }

    /**
     * Scope for search functionality
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
              ->orWhereHas('category', function ($category) use ($search) {
                  $category->where('name', 'like', '%' . $search . '%');
              });
        });
    }

    /**
     * Scope for filtering by category
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Get photo URL accessor
     */
    public function getPhotoUrlAttribute()
    {
        return $this->photo ? asset($this->photo) : null;
    }

    /**
     * Get the appropriate price based on order type and partner
     * 
     * @param string $orderType - 'dine_in', 'take_away', or 'online'
     * @param int|null $partnerId - Partner ID for online orders
     * @return float
     */
    public function getAppropriatePrice($orderType = 'dine_in', $partnerId = null)
    {
        // For dine_in and take_away, always use default price
        if (in_array($orderType, ['dine_in', 'take_away'])) {
            return $this->price;
        }

        // For online orders, check if partner has special price
        if ($orderType === 'online' && $partnerId) {
            $partnerPrice = ProductPartnerPrice::getPriceForPartner($this->id, $partnerId);
            if ($partnerPrice !== null) {
                return $partnerPrice;
            }
        }

        // Fallback to default price
        return $this->price;
    }

    /**
     * Get formatted appropriate price
     */
    public function getFormattedAppropriatePriceAttribute($orderType = 'dine_in', $partnerId = null)
    {
        $price = $this->getAppropriatePrice($orderType, $partnerId);
        return 'Rp ' . number_format($price, 0, ',', '.');
    }

    /**
     * Check if this product has partner price for given order type and partner
     * 
     * @param string $orderType - 'dine_in', 'take_away', or 'online'
     * @param int|null $partnerId - Partner ID for online orders
     * @return bool
     */
    public function hasPartnerPrice($orderType = 'dine_in', $partnerId = null)
    {
        // For dine_in and take_away, no partner pricing
        if (in_array($orderType, ['dine_in', 'take_away'])) {
            return false;
        }

        // For online orders, check if partner has special price
        if ($orderType === 'online' && $partnerId) {
            $partnerPrice = ProductPartnerPrice::getPriceForPartner($this->id, $partnerId);
            return $partnerPrice !== null;
        }

        return false;
    }

    /**
     * Get applicable discount for this product based on order type
     * 
     * @param string $orderType - 'dine_in', 'take_away', or 'online'
     * @return \App\Models\Discount|null
     */
    public function getApplicableDiscount($orderType = 'dine_in')
    {
        return Discount::where('type', 'product')
                      ->where('product_id', $this->id)
                      ->where('is_active', true)
                      ->forOrderType($orderType)
                      ->first();
    }

    /**
     * Get discounted price for this product based on order type
     * 
     * @param string $orderType - 'dine_in', 'take_away', or 'online'
     * @param int|null $partnerId - Partner ID for online orders
     * @return float
     */
    public function getDiscountedPrice($orderType = 'dine_in', $partnerId = null)
    {
        $basePrice = $this->getAppropriatePrice($orderType, $partnerId);
        $discount = $this->getApplicableDiscount($orderType);
        
        if ($discount) {
            $discountAmount = $discount->calculateDiscount($basePrice);
            return max(0, $basePrice - $discountAmount);
        }
        
        return $basePrice;
    }

    /**
     * Check if this product has an active discount for given order type
     * 
     * @param string $orderType - 'dine_in', 'take_away', or 'online'
     * @return bool
     */
    public function hasActiveDiscount($orderType = 'dine_in')
    {
        return $this->getApplicableDiscount($orderType) !== null;
    }

    /**
     * Get discount amount for this product based on order type
     * 
     * @param string $orderType - 'dine_in', 'take_away', or 'online'
     * @param int|null $partnerId - Partner ID for online orders
     * @return float
     */
    public function getDiscountAmount($orderType = 'dine_in', $partnerId = null)
    {
        $basePrice = $this->getAppropriatePrice($orderType, $partnerId);
        $discount = $this->getApplicableDiscount($orderType);
        
        if ($discount) {
            return $discount->calculateDiscount($basePrice);
        }
        
        return 0;
    }

    /**
     * Get formatted discounted price
     */
    public function getFormattedDiscountedPrice($orderType = 'dine_in', $partnerId = null)
    {
        return 'Rp ' . number_format($this->getDiscountedPrice($orderType, $partnerId), 0, ',', '.');
    }

    /**
     * Check if this product is a package (has components)
     */
    public function isPackageProduct()
    {
        return $this->activeComponents()->exists();
    }

    /**
     * Check if this product is used as a component in packages
     */
    public function isComponentProduct()
    {
        return $this->packagesUsingThisComponent()->where('is_active', true)->exists();
    }

    /**
     * Check if package has enough stock - SIMPLIFIED for KasirKabuki (no stock management)
     */
    public function hasEnoughStockForPackage($quantity = 1)
    {
        // KasirKabuki tidak menggunakan stock management
        // Semua produk dianggap selalu tersedia
        return true;
    }

    /**
     * Get simplified stock status for KasirKabuki
     */
    public function getPackageStockStatus($quantity = 1)
    {
        return [
            'is_package' => $this->isPackageProduct(),
            'is_sufficient' => true, // Selalu cukup karena tidak ada stock management
            'current_stock' => null, // Tidak ada stock tracking
        ];
    }

    /**
     * Get simplified stock information for KasirKabuki
     */
    public function getStockInfo()
    {
        $info = [
            'product_id' => $this->id,
            'product_name' => $this->name,
            'is_package' => $this->isPackageProduct(),
            'is_component' => $this->isComponentProduct(),
            'current_stock' => null, // Tidak ada stock tracking
            'stock_management_enabled' => false,
        ];

        if ($this->isPackageProduct()) {
            $info['package_info'] = [
                'components' => $this->activeComponents->map(function ($component) {
                    return [
                        'component_id' => $component->component_product_id,
                        'component_name' => $component->componentProduct->name,
                        'quantity_per_package' => $component->quantity_per_package,
                    ];
                })
            ];
        }

        if ($this->isComponentProduct()) {
            $info['used_in_packages'] = $this->packagesUsingThisComponent
                ->where('is_active', true)
                ->map(function ($component) {
                    return [
                        'package_id' => $component->package_product_id,
                        'package_name' => $component->packageProduct->name,
                        'quantity_needed' => $component->quantity_per_package
                    ];
                });
        }

        return $info;
    }
}
