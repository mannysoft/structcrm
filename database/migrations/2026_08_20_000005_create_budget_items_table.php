<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets')->cascadeOnDelete();
            $table->foreignId('cost_code_id')->nullable()->constrained('cost_codes')->nullOnDelete();
            $table->foreignId('boq_item_id')->nullable()->constrained('boq_items')->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity', 12, 2)->default(0);
            $table->string('unit', 50)->default('lot');
            $table->bigInteger('unit_price')->default(0);
            $table->bigInteger('total')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_items');
    }
};
