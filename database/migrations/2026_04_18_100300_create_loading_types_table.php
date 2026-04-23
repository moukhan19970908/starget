<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loading_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Задняя, Боковая, Верхняя, Задняя+Боковая
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loading_types');
    }
};
