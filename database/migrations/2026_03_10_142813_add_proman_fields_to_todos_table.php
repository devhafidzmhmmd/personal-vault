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
        Schema::table('todos', function (Blueprint $table) {
            $table->foreignId('proman_project_id')->nullable()->after('shortcut_id')->constrained('proman_projects')->nullOnDelete();
            $table->dateTime('proman_submit_scheduled_at')->nullable()->after('position');
            $table->dateTime('proman_submitted_at')->nullable()->after('proman_submit_scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('todos', function (Blueprint $table) {
            $table->dropForeign(['proman_project_id']);
            $table->dropColumn(['proman_submit_scheduled_at', 'proman_submitted_at']);
        });
    }
};
