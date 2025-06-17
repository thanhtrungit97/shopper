<?php

declare(strict_types=1);

namespace Shopper\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Shopper\Core\Traits\HasSlug;

class Tax extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
        'is_active',
        'is_inclusive',
        'is_compound',
        'priority',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_inclusive' => 'boolean',
        'is_compound' => 'boolean',
        'priority' => 'integer',
        'metadata' => 'array',
    ];

    public function getTable(): string
    {
        return shopper_table('taxes');
    }

    /**
     * Get all tax rates for this tax
     */
    public function taxRates(): HasMany
    {
        return $this->hasMany(TaxRate::class);
    }

    /**
     * Get active tax rates for this tax
     */
    public function activeTaxRates(): HasMany
    {
        return $this->taxRates()
            ->where(function ($query) {
                $query->whereNull('effective_from')
                    ->orWhere('effective_from', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', now());
            });
    }

    /**
     * Get products that have this tax applied
     */
    public function products(): MorphToMany
    {
        return $this->morphedByMany(
            config('shopper.models.product'),
            'taxable',
            shopper_table('product_taxes')
        )->withPivot(['is_active', 'custom_rate', 'custom_fixed_amount', 'metadata']);
    }

    /**
     * Get product variants that have this tax applied
     */
    public function productVariants(): MorphToMany
    {
        return $this->morphedByMany(
            config('shopper.models.variant'),
            'taxable',
            shopper_table('product_taxes')
        )->withPivot(['is_active', 'custom_rate', 'custom_fixed_amount', 'metadata']);
    }

    /**
     * Get categories that have this tax applied
     */
    public function categories(): MorphToMany
    {
        return $this->morphedByMany(
            config('shopper.models.category'),
            'taxable',
            shopper_table('product_taxes')
        )->withPivot(['is_active', 'custom_rate', 'custom_fixed_amount', 'metadata']);
    }

    /**
     * Get tax rate for a specific zone and currency
     */
    public function getTaxRateForZone(?int $zoneId = null, ?int $currencyId = null): ?TaxRate
    {
        return $this->activeTaxRates()
            ->when($zoneId, fn ($query) => $query->where('zone_id', $zoneId))
            ->when($currencyId, fn ($query) => $query->where('currency_id', $currencyId))
            ->orderBy('is_default', 'desc')
            ->first();
    }

    /**
     * Check if tax is percentage type
     */
    public function isPercentage(): bool
    {
        return $this->type === 'percentage';
    }

    /**
     * Check if tax is fixed amount type
     */
    public function isFixedAmount(): bool
    {
        return $this->type === 'fixed_amount';
    }

    /**
     * Scope to get only active taxes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by priority for compound tax calculations
     */
    public function scopeOrderedByPriority($query)
    {
        return $query->orderBy('priority', 'asc')->orderBy('id', 'asc');
    }
}
