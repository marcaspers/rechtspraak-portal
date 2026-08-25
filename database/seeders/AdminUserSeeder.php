<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * There is no self-registration (briefing §2.6) — the first admin account is created here.
     * Override ADMIN_EMAIL / ADMIN_PASSWORD in .env for anything beyond local development, and
     * change the password immediately after first login.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => config('admin.email')],
            [
                'name' => 'Beheerder',
                'password' => config('admin.password'),
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
            ]
        );
    }
}
