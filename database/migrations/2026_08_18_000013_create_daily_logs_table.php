<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('log_date');
            $table->string('weather')->nullable();
            $table->integer('crew_count')->default(0);
            $table->float('labor_hours')->default(0);
            $table->text('equipment_usage')->nullable();
            $table->text('work_performed');
            $table->text('issues')->nullable();
            $table->text('safety_incidents')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'log_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_logs');
    }
};
