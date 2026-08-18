<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->renameColumn('name', 'title');
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('project_number')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();

            $table->unique(['company_id', 'project_number']);
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'project_number']);
            $table->dropConstrainedForeignId('manager_id');
            $table->dropColumn(['description', 'project_number']);
            $table->dropConstrainedForeignId('client_id');
            $table->renameColumn('title', 'name');
        });
    }
};
