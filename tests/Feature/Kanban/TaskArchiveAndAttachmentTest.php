<?php

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\Organization;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function boardFixture(): array {
    $user = User::factory()->create();
    $org = Organization::create(['name' => 'Org', 'slug' => 'org-archive']);
    $org->users()->attach($user->id, ['role' => 'org_admin']);
    $team = Team::create(['organization_id' => $org->id, 'name' => 'Team']);
    $team->users()->attach($user->id, ['role' => 'manager']);
    $board = Board::create(['organization_id' => $org->id, 'team_id' => $team->id, 'name' => 'Board']);
    $column = BoardColumn::create(['board_id' => $board->id, 'name' => 'Backlog', 'type' => 'backlog', 'position' => 1]);
    $task = Task::create([
        'organization_id' => $org->id,
        'board_id' => $board->id,
        'column_id' => $column->id,
        'title' => 'Task',
        'created_by' => $user->id,
    ]);

    return compact('user', 'board', 'task');
}

it('archives and unarchives task', function () {
    $f = boardFixture();

    $this->actingAs($f['user'])
        ->post(route('tasks.archive', $f['task']), ['archive_reason' => 'History'])
        ->assertSessionHas('status');

    expect($f['task']->fresh()->archived_at)->not->toBeNull();

    $this->actingAs($f['user'])
        ->post(route('tasks.unarchive', $f['task']))
        ->assertSessionHas('status');

    expect($f['task']->fresh()->archived_at)->toBeNull();
});

it('uploads and stores task attachment', function () {
    Storage::fake('private');
    $f = boardFixture();

    $this->actingAs($f['user'])
        ->post(route('tasks.attachments.store', $f['task']), [
            'attachment' => UploadedFile::fake()->create('instrukcja.pdf', 200, 'application/pdf'),
        ])
        ->assertSessionHas('status');

    expect(TaskAttachment::query()->where('task_id', $f['task']->id)->exists())->toBeTrue();
    $path = TaskAttachment::query()->where('task_id', $f['task']->id)->first()->path;
    Storage::disk('private')->assertExists($path);
});
