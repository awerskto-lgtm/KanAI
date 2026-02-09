<?php

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;

it('org admin can create team and board with custom intermediate states', function () {
    $admin = User::factory()->create();
    $org = Organization::create(['name' => 'Org', 'slug' => 'org-mgmt']);
    $org->users()->attach($admin->id, ['role' => 'org_admin']);

    $this->actingAs($admin)
        ->post(route('teams.store'), [
            'organization_id' => $org->id,
            'name' => 'Proces A',
        ])
        ->assertSessionHas('status');

    $team = Team::query()->where('name', 'Proces A')->firstOrFail();

    $this->actingAs($admin)
        ->post(route('boards.store'), [
            'team_id' => $team->id,
            'name' => 'Linia 12',
            'columns' => "Backlog\nAnaliza\nImplementacja\nWalidacja\nDone",
        ])
        ->assertRedirect();

    $board = Board::query()->where('name', 'Linia 12')->firstOrFail();

    expect(BoardColumn::query()->where('board_id', $board->id)->where('name', 'Analiza')->exists())->toBeTrue();
});

it('manager can add intermediate column to board', function () {
    $user = User::factory()->create();
    $org = Organization::create(['name' => 'Org2', 'slug' => 'org2']);
    $org->users()->attach($user->id, ['role' => 'member']);

    $team = Team::create(['organization_id' => $org->id, 'name' => 'Team 2']);
    $team->users()->attach($user->id, ['role' => 'manager']);

    $board = Board::create(['organization_id' => $org->id, 'team_id' => $team->id, 'name' => 'Board 2']);
    BoardColumn::create(['board_id' => $board->id, 'name' => 'Backlog', 'type' => 'backlog', 'position' => 1]);

    $this->actingAs($user)
        ->post(route('boards.columns.store', $board), [
            'name' => 'Kontrola jakości',
            'type' => 'custom',
            'wip_limit' => 2,
        ])
        ->assertSessionHas('status');

    expect(BoardColumn::query()->where('board_id', $board->id)->where('name', 'Kontrola jakości')->exists())->toBeTrue();
});
