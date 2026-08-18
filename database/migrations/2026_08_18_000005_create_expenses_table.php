<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('category', 50);
            $table->bigInteger('amount')->default(0);
            $table->char('currency', 3)->default('USD');
            $table->date('date');
            $table->string('receipt_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
