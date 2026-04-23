<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique(); // REQ-{YYYY}-{0000}-KZ
            $table->foreignId('author_id')->constrained('users');
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('contract_id')->nullable()->constrained('contracts')->nullOnDelete();
            $table->string('shipper')->nullable();
            $table->string('consignee')->nullable();
            $table->date('departure_date')->nullable();
            $table->date('arrival_date')->nullable();
            $table->foreignId('departure_city_id')->constrained('cities');
            $table->foreignId('destination_city_id')->constrained('cities');
            $table->text('loading_address')->nullable();
            $table->text('unloading_address')->nullable();
            $table->string('contact_loading')->nullable();
            $table->string('contact_unloading')->nullable();
            $table->string('cargo_name')->nullable();
            $table->foreignId('loading_type_id')->nullable()->constrained('loading_types')->nullOnDelete();
            $table->text('special_conditions')->nullable();
            $table->decimal('weight', 8, 2)->default(0);
            $table->decimal('volume', 8, 2)->default(0);
            $table->decimal('cargo_cost', 12, 2)->default(0);
            $table->foreignId('cargo_currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->decimal('client_rate', 12, 2)->default(0);
            $table->boolean('client_rate_vat')->default(false);
            $table->foreignId('client_rate_currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->decimal('client_rate_exchange', 10, 4)->nullable();
            $table->text('comment')->nullable();
            $table->enum('status', [
                'draft', 'open', 'in_transit', 'closed',
                'client_refusal', 'our_refusal', 'mutual_refusal'
            ])->default('open');
            $table->foreignId('refusal_reason_id')->nullable()->constrained('refusal_reasons')->nullOnDelete();
            $table->text('refusal_comment')->nullable();
            $table->integer('planned_transportations_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
