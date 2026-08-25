<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('rulings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('feed_id')->constrained()->cascadeOnDelete();
            $table->string('ecli')->nullable()->unique();
            $table->string('source_guid')->nullable()->unique();
            $table->string('title');
            $table->string('instantie')->nullable();
            $table->string('rechtsgebied')->nullable();
            $table->timestamp('published_at');
            $table->string('source_url');
            $table->text('rss_summary')->nullable();
            $table->string('status')->default('pending');
            $table->longText('full_text')->nullable();
            $table->timestamp('full_text_fetched_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('published_at');
            $table->index('status');
            $table->index(['status', 'published_at']);
        });

        // Postgres full-text search column, kept out of the Eloquent-managed column set and
        // refreshed explicitly (see App\Services\Search\RulingSearchIndexer) whenever a ruling's
        // title or current summary changes.
        DB::statement("ALTER TABLE rulings ADD COLUMN search_vector tsvector");
        DB::statement('CREATE INDEX rulings_search_vector_index ON rulings USING GIN (search_vector)');
    }

    public function down(): void
    {
        Schema::dropIfExists('rulings');
    }
};
