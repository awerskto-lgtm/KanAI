<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Team;
use App\Services\RbacService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function store(Request $request, RbacService $rbac): RedirectResponse
    {
        $data = $request->validate([
            'organization_id' => ['required', 'uuid', 'exists:organizations,id'],
            'name' => ['required', 'string', 'max:120'],
        ]);

        $organization = Organization::findOrFail($data['organization_id']);
        abort_unless($rbac->isOrgAdmin($request->user(), $organization), 403);

        $team = Team::create([
            'organization_id' => $organization->id,
            'name' => $data['name'],
        ]);

        $team->users()->syncWithoutDetaching([$request->user()->id => ['role' => 'manager']]);

        return back()->with('status', 'Zespół został utworzony.');
    }
}
