<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Shopper\Core\Helpers\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->getTableName('order_item_taxes'), function (Blueprint $table): void {
            $this->addCommonFields($table);

            $this->addForeignKey($table, 'order_item_id', $this->getTableName('order_items'), false);
            $this->addForeignKey($table, 'tax_id', $this->getTableName('taxes'), false);
            
            $table->string('tax_name'); // Store tax name at time of order
            $table->string('tax_code'); // Store tax code at time of order
            $table->decimal('tax_rate', 8, 4); // Store tax rate at time of order
            $table->integer('tax_amount'); // Calculated tax amount in cents
            $table->integer('taxable_amount'); // Amount on which tax was calculated
            $table->integer('quantity'); // Quantity for this tax calculation
            $table->boolean('is_inclusive'); // Whether tax was inclusive or exclusive
            $table->boolean('is_compound'); // Whether this was a compound tax
            $table->json('metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->getTableName('order_item_taxes'));
    }
};
