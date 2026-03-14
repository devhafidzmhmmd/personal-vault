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
        Schema::table('users', function (Blueprint $table) {
            $table->string('proman_username')->nullable()->after('saved_locations');
            $table->text('proman_password')->nullable()->after('proman_username');
            $table->string('proman_api_key')->nullable()->after('proman_password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['proman_username', 'proman_password', 'proman_api_key']);
        });
    }
};
