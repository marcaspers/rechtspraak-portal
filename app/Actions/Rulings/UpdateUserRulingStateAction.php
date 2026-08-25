<?php

namespace App\Actions\Rulings;

use App\Models\Ruling;
use App\Models\User;
use App\Models\UserRulingState;

class UpdateUserRulingStateAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(User $user, Ruling $ruling, array $attributes): UserRulingState
    {
        if (($attributes['is_read'] ?? false) === true) {
            $attributes['read_at'] = now();
        }

        return UserRulingState::query()->updateOrCreate(
            ['user_id' => $user->id, 'ruling_id' => $ruling->id],
            $attributes,
        );
    }
}
