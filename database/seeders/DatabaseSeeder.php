<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\Organization;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Org Admin',
            'email' => 'admin@kanai.local',
        ]);

        $member = User::factory()->create([
            'name' => 'Team Member',
            'email' => 'member@kanai.local',
        ]);

        $organization = Organization::create([
            'name' => 'Fabryka Alfa',
            'slug' => Str::slug('Fabryka Alfa'),
        ]);

        $organization->users()->attach($admin->id, ['role' => 'org_admin']);
        $organization->users()->attach($member->id, ['role' => 'member']);

        $team = Team::create(['organization_id' => $organization->id, 'name' => 'Linia Produkcyjna A']);
        $team->users()->attach($admin->id, ['role' => 'manager']);
        $team->users()->attach($member->id, ['role' => 'member']);

        $board = Board::create([
            'organization_id' => $organization->id,
            'team_id' => $team->id,
            'name' => 'Sprint Utrzymanie Ruchu',
            'description' => 'Tablica operacyjna',
        ]);

        $columns = collect([
            ['name' => 'Backlog', 'type' => 'backlog', 'position' => 1, 'wip_limit' => null],
            ['name' => 'W toku', 'type' => 'doing', 'position' => 2, 'wip_limit' => 3],
            ['name' => 'Review', 'type' => 'review', 'position' => 3, 'wip_limit' => 2],
            ['name' => 'Done', 'type' => 'done', 'position' => 4, 'wip_limit' => null],
        ])->map(fn ($data) => BoardColumn::create(['board_id' => $board->id] + $data));

        Task::create([
            'organization_id' => $organization->id,
            'board_id' => $board->id,
            'column_id' => $columns[0]->id,
            'title' => 'Inspekcja linii pakowania',
            'description' => 'Sprawdź łożyska i taśmę transportera.',
            'type' => 'maintenance',
            'priority' => 'high',
            'created_by' => $admin->id,
            'assignee_id' => $member->id,
        ]);
    }
}
