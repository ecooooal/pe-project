<?php

namespace App\Policies;

use App\Models\Topic;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TopicPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Topic $topics): bool
    {
        return $user->hasPermissionTo('view topics');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create topics');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Topic $topics): bool
    {
        return $user->hasPermissionTo('update topics');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Topic $topics): bool
    {
        return $user->hasPermissionTo('destroy topics');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Topic $topics): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Topic $topics): bool
    {
        return false;
    }
}
