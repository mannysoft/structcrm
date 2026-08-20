<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('progress_report_id')->nullable()->constrained('progress_reports')->nullOnDelete();
            $table->string('billing_number');
            $table->date('period_start');
            $table->date('period_end');
            $table->bigInteger('previous_billing_amount')->default(0);
            $table->bigInteger('current_accomplishment_amount')->default(0);
            $table->bigInteger('gross_amount')->default(0);
            $table->decimal('retention_rate', 5, 4)->nullable();
            $table->bigInteger('retention_amount')->default(0);
            $table->bigInteger('other_deductions_amount')->default(0);
            $table->bigInteger('net_amount')->default(0);
            $table->char('currency', 3)->default('USD');
            $table->string('status', 50)->default('draft');
            $table->timestamp('certified_at')->nullable();
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'billing_number']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};
