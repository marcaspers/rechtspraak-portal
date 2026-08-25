<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('feed_fetch_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('feed_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('items_found')->default(0);
            $table->unsignedInteger('items_new')->default(0);
            $table->unsignedInteger('items_duplicate')->default(0);
            $table->string('status');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['feed_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_fetch_logs');
    }
};
