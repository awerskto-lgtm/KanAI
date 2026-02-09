<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\Team;
use App\Services\RbacService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BoardManagementController extends Controller
{
    public function store(Request $request, RbacService $rbac): RedirectResponse
    {
        $data = $request->validate([
            'team_id' => ['required', 'uuid', 'exists:teams,id'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'columns' => ['nullable', 'string', 'max:1000'],
        ]);

        $team = Team::with('organization')->findOrFail($data['team_id']);
        $role = $rbac->teamRole($request->user(), $team);
        $canCreate = $rbac->isOrgAdmin($request->user(), $team->organization) || in_array($role, ['manager', 'team_lead'], true);
        abort_unless($canCreate, 403);

        $board = Board::create([
            'organization_id' => $team->organization_id,
            'team_id' => $team->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $lines = collect(explode("\n", (string) ($data['columns'] ?? '')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values();

        if ($lines->isEmpty()) {
            $lines = collect(['Backlog', 'W toku', 'Review', 'Done']);
        }

        foreach ($lines as $index => $name) {
            BoardColumn::create([
                'board_id' => $board->id,
                'name' => $name,
                'type' => str_contains(strtolower($name), 'done') ? 'done' : (str_contains(strtolower($name), 'tok') ? 'doing' : 'custom'),
                'position' => $index + 1,
            ]);
        }

        return redirect()->route('boards.show', $board)->with('status', 'Tablica została utworzona.');
    }
}
