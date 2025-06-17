<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Shopper\Core\Helpers\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create($this->getTableName('taxes'), function (Blueprint $table): void {
            $this->addCommonFields($table);

            $table->string('name')->index();
            $table->string('code')->unique()->index();
            $table->string('type')->default('percentage'); // percentage, fixed_amount
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_inclusive')->default(false); // tax included in price or added on top
            $table->boolean('is_compound')->default(false); // compound tax (calculated on price + other taxes)
            $table->integer('priority')->default(0); // calculation order for compound taxes
            $table->json('metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->getTableName('taxes'));
    }
};
