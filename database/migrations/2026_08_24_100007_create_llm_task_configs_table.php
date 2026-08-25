<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('llm_task_configs', function (Blueprint $table): void {
            $table->id();
            $table->string('task_key')->unique();
            $table->string('provider');
            $table->string('model');
            $table->string('endpoint')->nullable();
            $table->jsonb('extra_params')->default('{}');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('llm_task_configs');
    }
};
