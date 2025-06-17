<?php

declare(strict_types=1);

namespace Shopper\Core\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Shopper\Core\Database\Factories\OrderItemFactory;

/**
 * @property-read int $id
 * @property string $name
 * @property int $quantity
 * @property int $unit_price_amount
 * @property int $total
 * @property string $sku
 * @property int $product_id
 * @property string $product_type
 * @property int $order_id
 * @property Order $order
 */
class OrderItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getTable(): string
    {
        return shopper_table('order_items');
    }

    protected static function newFactory(): OrderItemFactory
    {
        return OrderItemFactory::new();
    }

    protected function total(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->unit_price_amount * $this->quantity
        );
    }

    public function product(): MorphTo
    {
        return $this->morphTo();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Get all taxes applied to this order item
     */
    public function taxes(): HasMany
    {
        return $this->hasMany(OrderItemTax::class);
    }

    /**
     * Get total tax amount for this order item
     */
    public function getTotalTaxAmount(): int
    {
        return $this->taxes()->sum('tax_amount');
    }

    /**
     * Get formatted total tax amount
     */
    public function getFormattedTotalTaxAmount(): string
    {
        return shopper_money_format($this->getTotalTaxAmount(), $this->order->currency_code);
    }

    /**
     * Get order item total including taxes
     */
    public function getTotalWithTaxes(): int
    {
        return ($this->unit_price_amount * $this->quantity) + $this->getTotalTaxAmount();
    }

    /**
     * Get formatted order item total including taxes
     */
    public function getFormattedTotalWithTaxes(): string
    {
        return shopper_money_format($this->getTotalWithTaxes(), $this->order->currency_code);
    }

    /**
     * Get tax amount per unit
     */
    public function getTaxAmountPerUnit(): int
    {
        return $this->quantity > 0 ? (int) round($this->getTotalTaxAmount() / $this->quantity) : 0;
    }

    /**
     * Get formatted tax amount per unit
     */
    public function getFormattedTaxAmountPerUnit(): string
    {
        return shopper_money_format($this->getTaxAmountPerUnit(), $this->order->currency_code);
    }

    /**
     * Get unit price including taxes
     */
    public function getUnitPriceWithTaxes(): int
    {
        return $this->unit_price_amount + $this->getTaxAmountPerUnit();
    }

    /**
     * Get formatted unit price including taxes
     */
    public function getFormattedUnitPriceWithTaxes(): string
    {
        return shopper_money_format($this->getUnitPriceWithTaxes(), $this->order->currency_code);
    }

    /**
     * Get tax breakdown for this order item
     */
    public function getTaxBreakdown(): array
    {
        return $this->taxes()
            ->get()
            ->map(function ($tax) {
                return [
                    'tax_code' => $tax->tax_code,
                    'tax_name' => $tax->tax_name,
                    'tax_rate' => $tax->tax_rate,
                    'tax_amount' => $tax->tax_amount,
                    'taxable_amount' => $tax->taxable_amount,
                    'is_inclusive' => $tax->is_inclusive,
                    'is_compound' => $tax->is_compound,
                    'formatted_amount' => $tax->formatted_tax_amount,
                ];
            })
            ->toArray();
    }

    /**
     * Check if order item has any taxes
     */
    public function hasTaxes(): bool
    {
        return $this->taxes()->exists();
    }
}
