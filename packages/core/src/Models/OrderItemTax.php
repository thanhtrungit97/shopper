<?php

declare(strict_types=1);

namespace Shopper\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Shopper\Core\Models\OrderItem;

class OrderItemTax extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'tax_id',
        'tax_name',
        'tax_code',
        'tax_rate',
        'tax_amount',
        'taxable_amount',
        'quantity',
        'is_inclusive',
        'is_compound',
        'metadata',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:4',
        'tax_amount' => 'integer',
        'taxable_amount' => 'integer',
        'quantity' => 'integer',
        'is_inclusive' => 'boolean',
        'is_compound' => 'boolean',
        'metadata' => 'array',
    ];

    public function getTable(): string
    {
        return shopper_table('order_item_taxes');
    }

    /**
     * Get the order item that owns this tax
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the tax that this record references
     */
    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    /**
     * Get formatted tax amount
     */
    public function getFormattedTaxAmountAttribute(): string
    {
        return shopper_money_format($this->tax_amount, $this->orderItem->order->currency_code);
    }

    /**
     * Get formatted taxable amount
     */
    public function getFormattedTaxableAmountAttribute(): string
    {
        return shopper_money_format($this->taxable_amount, $this->orderItem->order->currency_code);
    }

    /**
     * Get tax percentage for display
     */
    public function getTaxPercentageAttribute(): string
    {
        return format_tax_rate((float) $this->tax_rate);
    }

    /**
     * Get tax amount per unit
     */
    public function getTaxAmountPerUnitAttribute(): int
    {
        return $this->quantity > 0 ? (int) round($this->tax_amount / $this->quantity) : 0;
    }
}
