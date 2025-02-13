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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone');
            $table->string('pdf_path');
            $table->string('status')->default('Bekliyor');
            $table->string('shipping_no')->nullable();
            $table->string('warehouse')->nullable();
            $table->string('shipping_region')->nullable();
            $table->string('address')->nullable();
            $table->string('shipping_date')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('kdv_amount', 10, 2)->default(0);
            $table->string('currency')->default('TL');
            $table->string('membership_status')->default('Üyeliksiz');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
