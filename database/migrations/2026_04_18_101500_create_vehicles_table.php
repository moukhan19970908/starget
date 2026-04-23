<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('owners');
            $table->foreignId('vehicle_type_id')->constrained('vehicle_types');
            $table->decimal('tonnage', 6, 1)->default(0);
            $table->decimal('volume', 6, 1)->default(0);
            $table->string('tractor_brand');
            $table->string('tractor_plate');
            $table->string('trailer_brand')->nullable();
            $table->string('trailer_plate')->nullable();
            $table->decimal('temperature_min', 5, 1)->nullable();
            $table->decimal('temperature_max', 5, 1)->nullable();
            $table->enum('status', ['active', 'in_transit', 'idle', 'repair'])->default('idle');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
