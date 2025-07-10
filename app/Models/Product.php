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
        'jenis_sate',
        'quantity_effect',
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
     * Get stock sate entries for this product (jika ini produk sate)
     */
    public function getStockSateForDate($date = null)
    {
        if (!$this->jenis_sate) {
            return null; // Non-sate products tidak memerlukan stock tracking
        }
        
        $date = $date ?: now()->format('Y-m-d');
        return \App\Models\StockSate::getStockForDateAndJenis($date, $this->jenis_sate);
    }

    /**
     * Get current stock - hanya untuk produk sate
     */
    public function getCurrentStock()
    {
        if (!$this->jenis_sate || !$this->quantity_effect) {
            return null; // Non-sate products tidak memerlukan stock tracking
        }

        $stockSateEntry = $this->getStockSateForDate();
        
        if ($stockSateEntry) {
            $availableStockSate = ($stockSateEntry->stok_awal ?? 0) - ($stockSateEntry->stok_terjual ?? 0);
            return floor($availableStockSate / $this->quantity_effect);
        }
        
        return 0;
    }

    /**
     * Check if this is a sate product that requires stock tracking
     */
    public function isSateProduct()
    {
        return !empty($this->jenis_sate) && !empty($this->quantity_effect);
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
     * Check if package has enough component stock - SIMPLIFIED for sate products only
     */
    public function hasEnoughStockForPackage($quantity = 1)
    {
        if (!$this->isPackageProduct()) {
            // For regular products, hanya check stock jika sate product
            if ($this->isSateProduct()) {
            return $this->getCurrentStock() >= $quantity;
            }
            return true; // Non-sate products tidak perlu stock check
        }

        // For package products, check all component stocks
        foreach ($this->activeComponents as $component) {
            if (!$component->hasEnoughComponentStock($quantity)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get component stock status for package products - SIMPLIFIED
     */
    public function getPackageStockStatus($quantity = 1)
    {
        if (!$this->isPackageProduct()) {
            $currentStock = $this->isSateProduct() ? $this->getCurrentStock() : null;
            $isSufficient = $this->isSateProduct() ? ($currentStock >= $quantity) : true;
            
            return [
                'is_package' => false,
                'is_sate_product' => $this->isSateProduct(),
                'current_stock' => $currentStock,
                'is_sufficient' => $isSufficient
            ];
        }

        $componentStatuses = [];
        $overallSufficient = true;

        foreach ($this->activeComponents as $component) {
            $status = $component->getComponentStockStatus($quantity);
            $componentStatuses[] = $status;
            
            if (!$status['is_sufficient']) {
                $overallSufficient = false;
            }
        }

        return [
            'is_package' => true,
            'components' => $componentStatuses,
            'is_sufficient' => $overallSufficient,
            'can_make_quantity' => $this->getMaxPackageQuantityFromComponents()
        ];
    }

    /**
     * Calculate maximum package quantity - SIMPLIFIED for sate products only
     */
    public function getMaxPackageQuantityFromComponents()
    {
        if (!$this->isPackageProduct()) {
            if ($this->isSateProduct()) {
            return $this->getCurrentStock();
            }
            return null; // Non-sate products tidak perlu stock calculation
        }

        $maxQuantity = PHP_INT_MAX;

        foreach ($this->activeComponents as $component) {
            if ($component->componentProduct->isSateProduct()) {
            $componentStock = $component->componentProduct->getCurrentStock();
            $possibleQuantity = floor($componentStock / $component->quantity_per_package);
            $maxQuantity = min($maxQuantity, $possibleQuantity);
            }
        }

        return $maxQuantity === PHP_INT_MAX ? null : $maxQuantity;
    }

    /**
     * DEPRECATED - Stock operations tidak diperlukan dengan simplified approach
     * Sate products hanya menggunakan StockSate system
     */
    public function reduceStockForSale($quantity, $userId, $transactionId = null, $notes = null)
    {
        // Stock reduction ditangani oleh StockSateService untuk sate products
        // Non-sate products tidak perlu stock tracking
        return null;
    }

    /**
     * DEPRECATED - Stock operations tidak diperlukan dengan simplified approach
     */
    public function returnStockForCancellation($quantity, $userId, $transactionId = null, $notes = null)
    {
        // Stock return ditangani oleh StockSateService untuk sate products
        // Non-sate products tidak perlu stock tracking
        return null;
    }

    /**
     * Get simplified stock information
     */
    public function getStockInfo()
    {
        $info = [
            'product_id' => $this->id,
            'product_name' => $this->name,
            'is_sate_product' => $this->isSateProduct(),
            'is_package' => $this->isPackageProduct(),
            'is_component' => $this->isComponentProduct(),
        ];

        if ($this->isSateProduct()) {
            $info['current_stock'] = $this->getCurrentStock();
            $info['jenis_sate'] = $this->jenis_sate;
            $info['quantity_effect'] = $this->quantity_effect;
        } else {
            $info['current_stock'] = null; // Non-sate products tidak memerlukan stock tracking
        }

        if ($this->isPackageProduct()) {
            $info['package_info'] = [
                'max_makeable' => $this->getMaxPackageQuantityFromComponents(),
                'components' => $this->activeComponents->map(function ($component) {
                    return [
                        'component_id' => $component->component_product_id,
                        'component_name' => $component->componentProduct->name,
                        'quantity_per_package' => $component->quantity_per_package,
                        'is_sate_product' => $component->componentProduct->isSateProduct(),
                        'current_stock' => $component->componentProduct->isSateProduct() 
                            ? $component->componentProduct->getCurrentStock() 
                            : null
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
