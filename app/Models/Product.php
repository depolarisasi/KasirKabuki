<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
     * Get stock logs untuk this product
     */
    public function stockLogs(): HasMany
    {
        return $this->hasMany(StockLog::class);
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
     * Get current stock using new StockLog system
     */
    public function getCurrentStock()
    {
        return \App\Models\StockLog::getCurrentStock($this->id);
    }

    /**
     * Check if package has enough component stock
     */
    public function hasEnoughStockForPackage($quantity = 1)
    {
        if (!$this->isPackageProduct()) {
            // For regular products, check normal stock
            return $this->getCurrentStock() >= $quantity;
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
     * Get component stock status for package products
     */
    public function getPackageStockStatus($quantity = 1)
    {
        if (!$this->isPackageProduct()) {
            return [
                'is_package' => false,
                'current_stock' => $this->getCurrentStock(),
                'is_sufficient' => $this->getCurrentStock() >= $quantity
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
     * Calculate maximum package quantity yang bisa dibuat dari component stocks
     */
    public function getMaxPackageQuantityFromComponents()
    {
        if (!$this->isPackageProduct()) {
            return $this->getCurrentStock();
        }

        $maxQuantity = PHP_INT_MAX;

        foreach ($this->activeComponents as $component) {
            $componentStock = $component->componentProduct->getCurrentStock();
            $possibleQuantity = floor($componentStock / $component->quantity_per_package);
            $maxQuantity = min($maxQuantity, $possibleQuantity);
        }

        return $maxQuantity === PHP_INT_MAX ? 0 : $maxQuantity;
    }

    /**
     * Reduce stock for package sale (affects component stocks)
     */
    public function reduceStockForSale($quantity, $userId, $transactionId = null, $notes = null)
    {
        if (!$this->isPackageProduct()) {
            // Regular product - reduce own stock
            return \App\Models\StockLog::logSale(
                $this->id,
                $userId,
                $quantity,
                $transactionId,
                $notes ?: "Penjualan produk {$this->name}"
            );
        }

        // Package product - reduce component stocks
        $movements = [];
        foreach ($this->activeComponents as $component) {
            $componentQuantity = $component->calculateTotalComponentQuantity($quantity);
            $movements[] = \App\Models\StockLog::logSale(
                $component->component_product_id,
                $userId,
                $componentQuantity,
                $transactionId,
                $notes ?: "Penjualan package {$this->name} - component {$component->componentProduct->name}"
            );
        }

        return $movements;
    }

    /**
     * Return stock for cancelled sale (reverse stock reduction)
     */
    public function returnStockForCancellation($quantity, $userId, $transactionId = null, $notes = null)
    {
        if (!$this->isPackageProduct()) {
            // Regular product - return own stock
            return \App\Models\StockLog::logCancellationReturn(
                $this->id,
                $userId,
                $quantity,
                $transactionId,
                $notes ?: "Return pembatalan {$this->name}"
            );
        }

        // Package product - return component stocks
        $movements = [];
        foreach ($this->activeComponents as $component) {
            $componentQuantity = $component->calculateTotalComponentQuantity($quantity);
            $movements[] = \App\Models\StockLog::logCancellationReturn(
                $component->component_product_id,
                $userId,
                $componentQuantity,
                $transactionId,
                $notes ?: "Return pembatalan package {$this->name} - component {$component->componentProduct->name}"
            );
        }

        return $movements;
    }

    /**
     * Get detailed stock information
     */
    public function getStockInfo()
    {
        $info = [
            'product_id' => $this->id,
            'product_name' => $this->name,
            'is_package' => $this->isPackageProduct(),
            'is_component' => $this->isComponentProduct(),
            'current_stock' => $this->getCurrentStock(),
        ];

        if ($this->isPackageProduct()) {
            $info['package_info'] = [
                'max_makeable' => $this->getMaxPackageQuantityFromComponents(),
                'components' => $this->activeComponents->map(function ($component) {
                    return [
                        'component_name' => $component->componentProduct->name,
                        'required_per_package' => $component->quantity_per_package,
                        'current_stock' => $component->componentProduct->getCurrentStock(),
                        'unit' => $component->unit
                    ];
                })
            ];
        }

        if ($this->isComponentProduct()) {
            $info['used_in_packages'] = $this->packagesUsingThisComponent()
                ->where('is_active', true)
                ->with('packageProduct')
                ->get()
                ->map(function ($usage) {
                    return [
                        'package_name' => $usage->packageProduct->name,
                        'quantity_needed' => $usage->quantity_per_package
                    ];
                });
        }

        return $info;
    }
}
