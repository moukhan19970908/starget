<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users');
            $table->enum('assigned_role', ['doc_manager']);
            $table->enum('type', ['create_client', 'create_contract', 'create_supplier']);
            $table->json('payload')->nullable();
            $table->enum('status', ['open', 'done'])->default('open');
            $table->enum('priority', ['normal', 'high'])->default('normal');
            $table->foreignId('application_id')->nullable()->constrained('applications')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
