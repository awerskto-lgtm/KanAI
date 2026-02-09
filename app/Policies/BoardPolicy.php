<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\User;
use App\Services\RbacService;

class BoardPolicy
{
    public function __construct(private readonly RbacService $rbac) {}

    public function view(User $user, Board $board): bool
    {
        return $board->organization->users()->where('users.id', $user->id)->exists();
    }

    public function update(User $user, Board $board): bool
    {
        return $this->rbac->canManageBoard($user, $board);
    }

    public function manageTasks(User $user, Board $board): bool
    {
        return $this->view($user, $board);
    }
}
