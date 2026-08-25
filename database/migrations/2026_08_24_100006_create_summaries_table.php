<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('summaries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ruling_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prompt_template_id')->nullable()->constrained()->nullOnDelete();
            $table->text('content');
            $table->text('relevance_note')->nullable();
            $table->string('provider');
            $table->string('model');
            $table->unsignedInteger('tokens_prompt')->default(0);
            $table->unsignedInteger('tokens_completion')->default(0);
            $table->boolean('is_current')->default(true);
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->index(['ruling_id', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('summaries');
    }
};
