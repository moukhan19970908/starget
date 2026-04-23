<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transportations', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique(); // TRN-{YYYY}-{0000}
            $table->foreignId('application_id')->constrained('applications');
            $table->string('organization')->nullable();
            $table->foreignId('client_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('logistic_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('client_contract_id')->nullable()->constrained('contracts')->nullOnDelete();
            $table->boolean('vat_kz')->default(false);
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('supplier_contract_id')->nullable()->constrained('contracts')->nullOnDelete();
            $table->integer('supplier_delay_days')->nullable();
            $table->decimal('supplier_rate', 12, 2)->nullable();
            $table->enum('supplier_rate_currency', ['KZT', 'USD'])->nullable();
            $table->string('call_photo_path')->nullable();
            $table->enum('status', ['draft', 'open', 'in_transit', 'completed'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transportations');
    }
};
