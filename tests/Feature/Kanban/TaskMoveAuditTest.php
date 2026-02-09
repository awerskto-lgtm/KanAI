<?php

use App\Models\ActivityLog;
use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\Organization;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;

it('stores activity log when task is moved', function () {
    $user = User::factory()->create();
    $org = Organization::create(['name' => 'Org', 'slug' => 'org-audit']);
    $org->users()->attach($user->id, ['role' => 'org_admin']);

    $team = Team::create(['organization_id' => $org->id, 'name' => 'Team']);
    $team->users()->attach($user->id, ['role' => 'manager']);

    $board = Board::create(['organization_id' => $org->id, 'team_id' => $team->id, 'name' => 'Board']);
    $from = BoardColumn::create(['board_id' => $board->id, 'name' => 'Backlog', 'type' => 'backlog', 'position' => 1]);
    $to = BoardColumn::create(['board_id' => $board->id, 'name' => 'Doing', 'type' => 'doing', 'position' => 2]);

    $task = Task::create([
        'organization_id' => $org->id,
        'board_id' => $board->id,
        'column_id' => $from->id,
        'title' => 'Move me',
        'created_by' => $user->id,
    ]);

    $this->actingAs($user)->post(route('tasks.move', $task), ['to_column_id' => $to->id])->assertSessionHas('status');

    expect(ActivityLog::query()->where('action', 'task.moved')->where('subject_id', $task->id)->exists())->toBeTrue();
});

it('rejects moving task to a column from another board', function () {
    $user = User::factory()->create();
    $org = Organization::create(['name' => 'Org', 'slug' => 'org-validate']);
    $org->users()->attach($user->id, ['role' => 'org_admin']);

    $team = Team::create(['organization_id' => $org->id, 'name' => 'Team']);
    $team->users()->attach($user->id, ['role' => 'manager']);

    $boardA = Board::create(['organization_id' => $org->id, 'team_id' => $team->id, 'name' => 'A']);
    $colA = BoardColumn::create(['board_id' => $boardA->id, 'name' => 'Backlog', 'type' => 'backlog', 'position' => 1]);

    $boardB = Board::create(['organization_id' => $org->id, 'team_id' => $team->id, 'name' => 'B']);
    $colB = BoardColumn::create(['board_id' => $boardB->id, 'name' => 'Other', 'type' => 'doing', 'position' => 1]);

    $task = Task::create([
        'organization_id' => $org->id,
        'board_id' => $boardA->id,
        'column_id' => $colA->id,
        'title' => 'Task',
        'created_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->from(route('boards.show', $boardA))
        ->post(route('tasks.move', $task), ['to_column_id' => $colB->id])
        ->assertSessionHasErrors('to_column_id');

    expect($task->fresh()->column_id)->toBe($colA->id);
});
