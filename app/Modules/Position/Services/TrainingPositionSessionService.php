<?php

namespace App\Modules\Position\Services;

use App\User;
use App\Modules\Position\Exceptions\PermissionsAlreadyGrantedException;
use App\Modules\Position\Exceptions\PermissionsAlreadyRevokedException;
use App\Modules\Position\TrainingPosition;
use Illuminate\Database\Eloquent\Collection;

class TrainingPositionSessionService
{
    public function activeTrainees(TrainingPosition $position): Collection
    {
        return $position->users()->get();
    }

    public function checkHasExistingPermissions(User $user, TrainingPosition $position): bool
    {
        $traineeUsers = $this->activeTrainees($position);

        // exit if no active trainees on position to save later queries.
        if ($traineeUsers->isEmpty()) {
            return false;
        }

        $userAssignedPermissions = $traineeUsers->where('user_id', '==', $user->id);

        return !$userAssignedPermissions->isEmpty();
    }

    public function grantPermissions(User $user, TrainingPosition $position): User
    {
        throw_if(
            $this->checkHasExistingPermissions($user, $position),
            PermissionsAlreadyGrantedException::class
        );

        $position->users()->attach($user->id);

        return $position->fresh()->users()->find($user->id);
    }

    public function revokePermissions(User $user, TrainingPosition $position): bool
    {
        throw_if(
            ! $this->checkHasExistingPermissions($user, $position),
            PermissionsAlreadyRevokedException::class
        );

        return $position->users()->detach($user->id);
    }
}