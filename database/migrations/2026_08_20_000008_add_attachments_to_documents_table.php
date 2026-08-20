<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('change_order_id')->nullable()->after('project_id')->constrained('change_orders')->nullOnDelete();
            $table->unsignedBigInteger('billing_id')->nullable()->after('change_order_id');

            $table->index('change_order_id');
            $table->index('billing_id');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['change_order_id']);
            $table->dropIndex(['billing_id']);
            $table->dropConstrainedForeignId('change_order_id');
            $table->dropColumn('billing_id');
        });
    }
};
