<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('llm_usage_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('task_key');
            $table->string('provider');
            $table->string('model');
            $table->foreignId('ruling_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('tokens_prompt')->default(0);
            $table->unsignedInteger('tokens_completion')->default(0);
            $table->unsignedInteger('tokens_total')->default(0);
            $table->decimal('cost_estimate', 10, 4)->nullable();
            $table->boolean('success');
            $table->text('error_message')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();

            $table->index(['task_key', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('llm_usage_logs');
    }
};
