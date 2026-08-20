<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('contract_number');
            $table->string('title');
            $table->bigInteger('contract_value_amount')->default(0);
            $table->char('contract_value_currency', 3)->default('USD');
            $table->date('signed_at')->nullable();
            $table->string('status', 50)->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'contract_number']);
            $table->unique('project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
