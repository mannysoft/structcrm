<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boq_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('cost_code_id')->nullable()->constrained('cost_codes')->nullOnDelete();
            $table->string('item_code', 50)->nullable();
            $table->string('description');
            $table->decimal('quantity', 12, 2)->default(0);
            $table->string('unit', 50)->default('lot');
            $table->bigInteger('unit_price')->default(0);
            $table->bigInteger('total')->default(0);
            $table->char('currency', 3)->default('USD');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boq_items');
    }
};
