<?php

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\Organization;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;

function kanbanFixture(): array {
    $user = User::factory()->create();
    $org = Organization::create(['name' => 'Org', 'slug' => 'org']);
    $org->users()->attach($user->id, ['role' => 'member']);
    $team = Team::create(['organization_id' => $org->id, 'name' => 'Team']);
    $team->users()->attach($user->id, ['role' => 'member']);
    $board = Board::create(['organization_id' => $org->id, 'team_id' => $team->id, 'name' => 'Board']);
    $doing = BoardColumn::create(['board_id' => $board->id, 'name' => 'Doing', 'type' => 'doing', 'position' => 1, 'wip_limit' => 1]);
    $done = BoardColumn::create(['board_id' => $board->id, 'name' => 'Done', 'type' => 'done', 'position' => 2]);

    return compact('user', 'org', 'team', 'board', 'doing', 'done');
}

it('blocks move when wip is full for member', function () {
    $f = kanbanFixture();

    Task::create([
        'organization_id' => $f['org']->id,
        'board_id' => $f['board']->id,
        'column_id' => $f['doing']->id,
        'title' => 'Existing',
        'created_by' => $f['user']->id,
    ]);

    $task = Task::create([
        'organization_id' => $f['org']->id,
        'board_id' => $f['board']->id,
        'column_id' => $f['done']->id,
        'title' => 'Move Me',
        'created_by' => $f['user']->id,
    ]);

    $this->actingAs($f['user'])
        ->post(route('tasks.move', $task), ['to_column_id' => $f['doing']->id])
        ->assertSessionHas('status');

    expect($task->fresh()->column_id)->toBe($f['done']->id);
});

it('allows manager to override wip', function () {
    $f = kanbanFixture();
    $f['team']->users()->updateExistingPivot($f['user']->id, ['role' => 'manager']);

    Task::create([
        'organization_id' => $f['org']->id,
        'board_id' => $f['board']->id,
        'column_id' => $f['doing']->id,
        'title' => 'Existing',
        'created_by' => $f['user']->id,
    ]);

    $task = Task::create([
        'organization_id' => $f['org']->id,
        'board_id' => $f['board']->id,
        'column_id' => $f['done']->id,
        'title' => 'Move Me',
        'created_by' => $f['user']->id,
    ]);

    $this->actingAs($f['user'])
        ->post(route('tasks.move', $task), ['to_column_id' => $f['doing']->id]);

    expect($task->fresh()->column_id)->toBe($f['doing']->id);
});

it('prevents done move if dependency is not done', function () {
    $f = kanbanFixture();

    $blockedBy = Task::create([
        'organization_id' => $f['org']->id,
        'board_id' => $f['board']->id,
        'column_id' => $f['doing']->id,
        'title' => 'Blocking',
        'created_by' => $f['user']->id,
    ]);

    $task = Task::create([
        'organization_id' => $f['org']->id,
        'board_id' => $f['board']->id,
        'column_id' => $f['doing']->id,
        'title' => 'Blocked',
        'created_by' => $f['user']->id,
    ]);

    $task->dependencies()->attach($blockedBy->id);

    $this->actingAs($f['user'])
        ->post(route('tasks.move', $task), ['to_column_id' => $f['done']->id]);

    expect($task->fresh()->column_id)->toBe($f['doing']->id);
});
