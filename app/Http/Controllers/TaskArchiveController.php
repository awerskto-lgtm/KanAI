<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TaskArchiveController extends Controller
{
    public function archive(Request $request, Task $task, ActivityLogService $activityLog): RedirectResponse
    {
        $this->authorize('update', $task);

        $data = $request->validate([
            'archive_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $old = ['archived_at' => $task->archived_at?->toIso8601String()];

        $task->update([
            'archived_at' => Carbon::now(),
            'archived_by' => $request->user()->id,
            'archive_reason' => $data['archive_reason'] ?? null,
        ]);

        $activityLog->log(
            $task->board->organization,
            $request->user(),
            'task.archived',
            Task::class,
            $task->id,
            $old,
            [
                'archived_at' => $task->archived_at?->toIso8601String(),
                'archive_reason' => $task->archive_reason,
            ],
        );

        return back()->with('status', 'Zadanie zostało zarchiwizowane.');
    }

    public function unarchive(Request $request, Task $task, ActivityLogService $activityLog): RedirectResponse
    {
        $this->authorize('update', $task);

        $old = [
            'archived_at' => $task->archived_at?->toIso8601String(),
            'archive_reason' => $task->archive_reason,
        ];

        $task->update([
            'archived_at' => null,
            'archived_by' => null,
            'archive_reason' => null,
        ]);

        $activityLog->log(
            $task->board->organization,
            $request->user(),
            'task.unarchived',
            Task::class,
            $task->id,
            $old,
            ['archived_at' => null],
        );

        return back()->with('status', 'Zadanie przywrócone z archiwum.');
    }
}
