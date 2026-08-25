<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('user_ruling_states', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ruling_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_favorite')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'ruling_id']);
            $table->index(['user_id', 'is_archived']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_ruling_states');
    }
};
