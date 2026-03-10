<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('passwords', function (Blueprint $table) {
            $table->foreignId('prefix_id')->nullable()->after('workspace_id')->constrained('password_prefixes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('passwords', function (Blueprint $table) {
            $table->dropForeign(['prefix_id']);
        });
    }
};
