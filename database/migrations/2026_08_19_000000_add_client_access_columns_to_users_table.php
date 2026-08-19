<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Clean up the interim client_users table created before pivoting to a
        // single users table with a type discriminator.
        Schema::dropIfExists('client_users');

        Schema::table('users', function (Blueprint $table) {
            $table->string('type')->default('staff')->index();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
            $table->dropColumn('type');
        });
    }
};
