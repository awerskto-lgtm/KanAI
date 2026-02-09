<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use App\Services\RbacService;

class TaskPolicy
{
    public function __construct(private readonly RbacService $rbac) {}

    public function view(User $user, Task $task): bool
    {
        return $task->board->organization->users()->where('users.id', $user->id)->exists();
    }

    public function move(User $user, Task $task): bool
    {
        return $this->view($user, $task);
    }

    public function update(User $user, Task $task): bool
    {
        return $this->rbac->canManageBoard($user, $task->board) || $task->assignee_id === $user->id;
    }
}
