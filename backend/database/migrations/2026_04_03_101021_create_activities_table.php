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
        Schema::create('activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->uuidMorphs('subject'); // subject_type + subject_id (polymorphic)
            $table->string('event');         // created | updated | deleted | stage_changed …
            $table->jsonb('payload')->default('{}'); // before/after snapshot or metadata
            $table->timestamp('created_at'); // no updated_at — immutable by design

            $table->index('tenant_id');
            $table->index(['tenant_id', 'subject_type', 'subject_id']);
            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'event']);
            $table->index(['tenant_id', 'created_at']);
        });

        DB::statement('CREATE INDEX activities_payload_gin ON activities USING GIN (payload)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
