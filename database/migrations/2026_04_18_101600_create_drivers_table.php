<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['individual', 'legal'])->default('individual');
            $table->string('full_name');
            $table->string('phone', 30)->nullable();
            $table->string('iin', 20)->nullable();
            $table->string('license_classes', 50)->nullable(); // "A, B, C, CE"
            $table->enum('status', ['active', 'on_trip', 'reserve', 'sick'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
