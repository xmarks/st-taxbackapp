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
        Schema::create('receipt_tax_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scanned_receipt_id')->constrained()->onDelete('cascade');

            $table->bigInteger('tax_fiscal_id'); // id from sameTaxes
            $table->integer('number_of_items');
            $table->decimal('price_before_vat', 10, 2);
            $table->decimal('vat_rate', 5, 2);
            $table->decimal('vat_amount', 10, 2);
            $table->boolean('exempt_from_vat')->default(false);

            $table->timestamps();

            $table->index(['scanned_receipt_id', 'vat_rate']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipt_tax_summaries');
    }
};
