<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable()->change();
            $table->foreignId('vehicle_type_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable(false)->change();
            $table->foreignId('vehicle_type_id')->nullable(false)->change();
        });
    }
};
