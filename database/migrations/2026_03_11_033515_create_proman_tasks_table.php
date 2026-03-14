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
        Schema::create('proman_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('todo_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('id_task');
            $table->string('id_project');
            $table->json('response_data')->nullable();
            $table->dateTime('progress_completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proman_tasks');
    }
};
