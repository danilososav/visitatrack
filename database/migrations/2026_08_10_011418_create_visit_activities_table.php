<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['visit_id', 'activity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_activities');
    }
};
