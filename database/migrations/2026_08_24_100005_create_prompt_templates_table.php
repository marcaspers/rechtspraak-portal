<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('prompt_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('task_key');
            $table->string('name');
            $table->text('content');
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('task_key');
        });

        // Guarantee exactly one active template per task at the database level, so
        // PromptTemplateRepository::active() can never resolve an ambiguous result.
        DB::statement(
            'CREATE UNIQUE INDEX prompt_templates_active_per_task ON prompt_templates (task_key) WHERE is_active'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('prompt_templates');
    }
};
