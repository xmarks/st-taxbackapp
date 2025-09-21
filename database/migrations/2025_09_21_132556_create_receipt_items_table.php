<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scanned_receipt_id')->constrained()->onDelete('cascade');

            $table->bigInteger('item_fiscal_id'); // id from API
            $table->string('name', 255);
            $table->string('code', 50);
            $table->string('unit', 10); // COPE, etc
            $table->decimal('quantity', 10, 3);

            // Pricing
            $table->decimal('unit_price_before_vat', 15, 2);
            $table->decimal('unit_price_after_vat', 15, 2);
            $table->decimal('price_before_vat', 15, 2);
            $table->decimal('price_after_vat', 15, 2);

            // VAT details
            $table->decimal('vat_rate', 5, 2); // 20.00 for 20%
            $table->decimal('vat_amount', 15, 2);
            $table->boolean('exempt_from_vat')->default(false);

            // Discounts
            $table->decimal('rebate', 15, 2)->default(0);
            $table->boolean('rebate_reducing')->default(true);

            $table->boolean('investment')->default(false);

            $table->timestamps();

            $table->index(['scanned_receipt_id', 'vat_rate']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipt_items');
    }
};
