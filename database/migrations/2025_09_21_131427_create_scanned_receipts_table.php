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
        Schema::create('scanned_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('seller_id')->constrained()->onDelete('cascade');

            // Primary identifiers from API
            $table->bigInteger('fiscal_id'); // id from API response
            $table->string('iic', 50)->unique(); // Internal Invoice Code - unique globally
            $table->string('fic', 50); // Fiscal Invoice Code
            $table->string('invoice_number', 100); // invoiceNumber
            $table->integer('invoice_order_number'); // invoiceOrderNumber

            // Business unit identifiers
            $table->string('business_unit', 50);
            $table->string('cash_register', 50);
            $table->string('tcr_code', 50);
            $table->string('operator_code', 50)->nullable();
            $table->string('software_code', 50)->nullable();

            // Dates
            $table->timestamp('receipt_created_at'); // dateTimeCreated from API
            $table->timestamp('scanned_at'); // When user scanned it

            // Financial totals
            $table->decimal('total_price', 15, 2); // totalPrice
            $table->decimal('total_price_without_vat', 15, 2); // totalPriceWithoutVAT
            $table->decimal('total_vat_amount', 15, 2); // totalVATAmount
            $table->decimal('tax_free_amount', 15, 2)->default(0); // taxFreeAmt

            // Invoice details
            $table->string('invoice_type', 20); // CASH, etc
            $table->integer('invoice_version')->default(3);
            $table->boolean('is_einvoice')->default(false);
            $table->boolean('simplified_invoice')->default(false);

            // QR scan data
            $table->text('original_url'); // Full QR code URL
            $table->decimal('url_price', 15, 2); // Price from URL (for validation)

            // Store full API response for future reference
            $table->json('raw_api_response');

            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'scanned_at']);
            $table->index('receipt_created_at');
            $table->index(['seller_id', 'receipt_created_at']);
            $table->index('total_vat_amount'); // Important for VAT calculations
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scanned_receipts');
    }
};
