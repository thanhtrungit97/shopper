<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Shopper\Core\Helpers\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->getTableName('product_taxes'), function (Blueprint $table): void {
            $this->addCommonFields($table);

            $table->morphs('taxable'); // Can be applied to products, product variants, categories, etc.
            $this->addForeignKey($table, 'tax_id', $this->getTableName('taxes'), false);
            
            $table->boolean('is_active')->default(true);
            $table->decimal('custom_rate', 8, 4)->nullable(); // Override default tax rate for this product
            $table->integer('custom_fixed_amount')->nullable(); // Override fixed amount for this product
            $table->json('metadata')->nullable();

            // Ensure unique combination of taxable and tax
            $table->unique(['taxable_type', 'taxable_id', 'tax_id'], 'product_taxes_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->getTableName('product_taxes'));
    }
};
