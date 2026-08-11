<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('worker_id')->constrained('users');
            $table->string('type'); // client_visit | machine_job
            $table->string('status')->default('traveling_to');
            // traveling_to | at_client | traveling_back | pending_approval | completed | cancelled

            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('machine_id')->nullable()->constrained('machines')->nullOnDelete();
            $table->string('ov_number')->nullable();
            $table->string('ot_number')->nullable();
            $table->text('notes')->nullable();

            $table->uuid('group_id')->nullable();
            $table->integer('group_order')->nullable();
            $table->integer('group_size')->nullable();

            $table->timestamp('departed_base_at')->nullable();
            $table->decimal('departed_base_lat', 10, 7)->nullable();
            $table->decimal('departed_base_lng', 10, 7)->nullable();

            $table->timestamp('arrived_client_at')->nullable();
            $table->decimal('arrived_client_lat', 10, 7)->nullable();
            $table->decimal('arrived_client_lng', 10, 7)->nullable();

            $table->timestamp('departed_client_at')->nullable();
            $table->decimal('departed_client_lat', 10, 7)->nullable();
            $table->decimal('departed_client_lng', 10, 7)->nullable();

            $table->timestamp('arrived_base_at')->nullable();
            $table->decimal('arrived_base_lat', 10, 7)->nullable();
            $table->decimal('arrived_base_lng', 10, 7)->nullable();

            $table->string('worker_signature_path')->nullable();
            $table->string('second_signer_name')->nullable();
            $table->string('second_signer_path')->nullable();

            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'type']);
            $table->index('group_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
