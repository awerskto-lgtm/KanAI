<?php

use App\Models\Board;
use App\Models\Organization;
use App\Models\Team;
use App\Models\User;

it('scopes board list to organization membership', function () {
    $member = User::factory()->create();
    $outsider = User::factory()->create();

    $org = Organization::create(['name' => 'Org A', 'slug' => 'org-a']);
    $org->users()->attach($member->id, ['role' => 'member']);
    $team = Team::create(['organization_id' => $org->id, 'name' => 'Team A']);
    $team->users()->attach($member->id, ['role' => 'member']);
    $board = Board::create(['organization_id' => $org->id, 'team_id' => $team->id, 'name' => 'Visible']);

    $this->actingAs($member)->get(route('boards.index'))->assertSee('Visible');
    $this->actingAs($outsider)->get(route('boards.index'))->assertDontSee('Visible');
});
