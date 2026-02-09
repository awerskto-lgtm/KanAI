<?php

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\Organization;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;

function boardFixtureForFilters(): array {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $org = Organization::create(['name' => 'Org', 'slug' => 'org-filters']);
    $org->users()->attach($user->id, ['role' => 'org_admin']);
    $org->users()->attach($other->id, ['role' => 'member']);
    $team = Team::create(['organization_id' => $org->id, 'name' => 'Team']);
    $team->users()->attach($user->id, ['role' => 'manager']);
    $team->users()->attach($other->id, ['role' => 'member']);
    $board = Board::create(['organization_id' => $org->id, 'team_id' => $team->id, 'name' => 'Board']);
    $backlog = BoardColumn::create(['board_id' => $board->id, 'name' => 'Backlog', 'type' => 'backlog', 'position' => 1]);

    return compact('user','other','org','team','board','backlog');
}

it('creates task from board quick form', function () {
    $f = boardFixtureForFilters();

    $this->actingAs($f['user'])
        ->post(route('boards.tasks.store', $f['board']), [
            'column_id' => $f['backlog']->id,
            'title' => 'Nowa zmiana procesu',
            'type' => 'maintenance',
            'priority' => 'high',
            'assignee_id' => $f['other']->id,
        ])
        ->assertSessionHas('status');

    expect(Task::query()->where('board_id', $f['board']->id)->where('title', 'Nowa zmiana procesu')->exists())->toBeTrue();
});

it('applies mine and due_soon filters', function () {
    Carbon::setTestNow('2026-01-02 08:00:00');
    $f = boardFixtureForFilters();

    Task::create([
        'organization_id' => $f['org']->id,
        'board_id' => $f['board']->id,
        'column_id' => $f['backlog']->id,
        'title' => 'Moje pilne',
        'type' => 'it',
        'priority' => 'critical',
        'due_at' => Carbon::now()->addDay(),
        'assignee_id' => $f['user']->id,
        'created_by' => $f['user']->id,
    ]);

    Task::create([
        'organization_id' => $f['org']->id,
        'board_id' => $f['board']->id,
        'column_id' => $f['backlog']->id,
        'title' => 'Nie moje',
        'type' => 'it',
        'priority' => 'low',
        'due_at' => Carbon::now()->addDays(7),
        'assignee_id' => $f['other']->id,
        'created_by' => $f['user']->id,
    ]);

    $this->actingAs($f['user'])
        ->get(route('boards.show', $f['board']).'?mine=1&due_soon=1')
        ->assertSee('Moje pilne')
        ->assertDontSee('Nie moje');

    Carbon::setTestNow();
});
