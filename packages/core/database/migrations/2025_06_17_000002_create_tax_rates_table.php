<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Shopper\Core\Helpers\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->getTableName('tax_rates'), function (Blueprint $table): void {
            $this->addCommonFields($table);

            $table->decimal('rate', 8, 4)->index(); // Tax rate (e.g., 10.25 for 10.25%)
            $table->integer('fixed_amount')->nullable(); // For fixed amount taxes (in cents)
            $table->boolean('is_default')->default(false);
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            
            $this->addForeignKey($table, 'tax_id', $this->getTableName('taxes'), false);
            $this->addForeignKey($table, 'zone_id', $this->getTableName('zones'), true);
            $this->addForeignKey($table, 'currency_id', $this->getTableName('currencies'), true);

            // Ensure unique combination of tax, zone, and currency for active periods
            $table->unique(['tax_id', 'zone_id', 'currency_id', 'effective_from'], 'tax_rates_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->getTableName('tax_rates'));
    }
};
