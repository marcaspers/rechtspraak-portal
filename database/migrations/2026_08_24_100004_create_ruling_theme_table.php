<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ruling_theme', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ruling_id')->constrained()->cascadeOnDelete();
            $table->foreignId('theme_id')->constrained()->cascadeOnDelete();
            $table->string('match_type');
            $table->float('relevance_score')->nullable();
            $table->text('relevance_reason')->nullable();
            $table->jsonb('matched_keywords')->nullable();
            $table->timestamps();

            $table->unique(['ruling_id', 'theme_id']);
            $table->index('theme_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ruling_theme');
    }
};
