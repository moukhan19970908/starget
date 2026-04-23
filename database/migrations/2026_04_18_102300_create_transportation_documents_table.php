<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transportation_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transportation_id')->constrained('transportations')->cascadeOnDelete();
            $table->enum('type', ['driver', 'accompanying']);
            $table->string('file_path');
            $table->string('original_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transportation_documents');
    }
};
