<?php

use App\Enums\UserRole;
use App\Models\User;

it('blocks non-admin users from the admin section', function () {
    $user = User::factory()->create(['role' => UserRole::User]);

    $this->actingAs($user)->get('/admin')->assertForbidden();
});

it('allows admin users into the admin section', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)->get('/admin')->assertOk();
});

it('redirects guests to login', function () {
    $this->get('/admin')->assertRedirect('/login');
});
