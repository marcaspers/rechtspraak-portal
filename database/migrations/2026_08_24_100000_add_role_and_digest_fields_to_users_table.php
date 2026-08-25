<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default('user')->after('password');
            $table->string('digest_frequency')->default('daily')->after('role');
            $table->timestamp('last_digest_sent_at')->nullable()->after('digest_frequency');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['role', 'digest_frequency', 'last_digest_sent_at']);
        });
    }
};
