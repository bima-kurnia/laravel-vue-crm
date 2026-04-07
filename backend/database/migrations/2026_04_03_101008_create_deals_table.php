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
        Schema::create('deals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignUuid('owner_id')->constrained('users')->restrictOnDelete();
            $table->string('title');
            $table->decimal('value', 15, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('open'); // open | won | lost | stalled
            $table->string('stage')->default('prospecting');
            // prospecting | qualification | proposal | negotiation | closed
            $table->date('expected_close_date')->nullable();
            $table->jsonb('custom_data')->default('{}');
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'stage']);
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'owner_id']);
            $table->index(['tenant_id', 'expected_close_date']);
        });

        DB::statement('CREATE INDEX deals_custom_data_gin ON deals USING GIN (custom_data)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
