<?php

namespace App\Services;

use App\Models\Board;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;

class RbacService
{
    public function isOrgAdmin(User $user, Organization $organization): bool
    {
        if ($user->is_super_admin) return true;

        return $organization->users()
            ->where('users.id', $user->id)
            ->wherePivot('role', 'org_admin')
            ->exists();
    }

    public function teamRole(User $user, Team $team): ?string
    {
        return $team->users()->where('users.id', $user->id)->first()?->pivot?->role;
    }

    public function canManageBoard(User $user, Board $board): bool
    {
        $role = $this->teamRole($user, $board->team);

        return $this->isOrgAdmin($user, $board->organization) || in_array($role, ['manager', 'team_lead'], true);
    }
}
