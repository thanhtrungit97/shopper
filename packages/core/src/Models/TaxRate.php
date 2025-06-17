<?php

declare(strict_types=1);

namespace Shopper\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Shopper\Core\Models\Currency;
use Shopper\Core\Models\Zone;

class TaxRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_id',
        'zone_id',
        'currency_id',
        'rate',
        'fixed_amount',
        'is_default',
        'effective_from',
        'effective_until',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'fixed_amount' => 'integer',
        'is_default' => 'boolean',
        'effective_from' => 'date',
        'effective_until' => 'date',
    ];

    public function getTable(): string
    {
        return shopper_table('tax_rates');
    }

    /**
     * Get the tax that owns this rate
     */
    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    /**
     * Get the zone for this tax rate
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * Get the currency for this tax rate
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Check if this tax rate is currently effective
     */
    public function isEffective(): bool
    {
        $now = now()->toDateString();

        $fromCheck = is_null($this->effective_from) || $this->effective_from <= $now;
        $untilCheck = is_null($this->effective_until) || $this->effective_until >= $now;

        return $fromCheck && $untilCheck;
    }

    /**
     * Get the effective rate value
     */
    public function getEffectiveRate(): float
    {
        if ($this->tax->isFixedAmount()) {
            return (float) $this->fixed_amount;
        }

        return (float) $this->rate;
    }

    /**
     * Calculate tax amount for a given base amount
     */
    public function calculateTaxAmount(int $baseAmount): int
    {
        if ($this->tax->isFixedAmount()) {
            return $this->fixed_amount ?? 0;
        }

        return (int) round($baseAmount * ($this->rate / 100));
    }

    /**
     * Scope to get only effective tax rates
     */
    public function scopeEffective($query)
    {
        $now = now()->toDateString();

        return $query->where(function ($query) use ($now) {
            $query->whereNull('effective_from')
                ->orWhere('effective_from', '<=', $now);
        })->where(function ($query) use ($now) {
            $query->whereNull('effective_until')
                ->orWhere('effective_until', '>=', $now);
        });
    }

    /**
     * Scope to get default tax rates
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to filter by zone
     */
    public function scopeForZone($query, ?int $zoneId)
    {
        if ($zoneId) {
            return $query->where('zone_id', $zoneId);
        }

        return $query->whereNull('zone_id');
    }

    /**
     * Scope to filter by currency
     */
    public function scopeForCurrency($query, ?int $currencyId)
    {
        if ($currencyId) {
            return $query->where('currency_id', $currencyId);
        }

        return $query->whereNull('currency_id');
    }
}
