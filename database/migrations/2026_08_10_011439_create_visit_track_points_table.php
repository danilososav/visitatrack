<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_track_points', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->string('leg'); // to_client | to_base
            $table->timestamp('recorded_at');

            $table->index(['visit_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_track_points');
    }
};
