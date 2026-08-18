<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->string('estimate_number')->nullable();
            $table->bigInteger('subtotal')->default(0);
            $table->bigInteger('tax_amount')->default(0);

            $table->unique(['company_id', 'estimate_number']);
        });
    }

    public function down(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'estimate_number']);
            $table->dropColumn(['subtotal', 'tax_amount', 'estimate_number']);
        });
    }
};
