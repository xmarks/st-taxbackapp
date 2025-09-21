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
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->string('id_num', 20)->unique(); // NIPT (issuerTaxNumber)
            $table->string('id_type', 10)->default('NUIS');
            $table->string('name', 255);
            $table->string('address', 255)->nullable();
            $table->string('town', 100)->nullable();
            $table->string('country', 10)->default('ALB');
            $table->timestamps();

            // Index for performance
            $table->index(['country', 'town']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
