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
            $table->string('proman_user_id')->nullable()->after('master_password_salt');
            $table->string('proman_token')->nullable()->after('proman_user_id');
            $table->json('saved_locations')->nullable()->after('proman_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['proman_user_id', 'proman_token', 'saved_locations']);
        });
    }
};
