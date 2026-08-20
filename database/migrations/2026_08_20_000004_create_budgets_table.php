<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('type', 50)->default('original');
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_current')->default(false);
            $table->string('status', 50)->default('draft');
            $table->char('currency', 3)->default('USD');
            $table->bigInteger('forecast_amount')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
