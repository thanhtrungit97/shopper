<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Shopper\Core\Helpers\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->getTableName('orders'), function (Blueprint $table): void {
            $table->after('price_amount', function (Blueprint $table): void {
                $table->integer('tax_amount')->default(0)->comment('Total tax amount in cents');
                $table->integer('total_amount')->nullable()->comment('Total amount including taxes in cents');
            });
        });
    }

    public function down(): void
    {
        Schema::table($this->getTableName('orders'), function (Blueprint $table): void {
            $table->dropColumn(['tax_amount', 'total_amount']);
        });
    }
};
