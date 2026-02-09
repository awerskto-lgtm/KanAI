<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Task;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function store(Request $request, Board $board, ActivityLogService $activityLog): RedirectResponse
    {
        $this->authorize('manageTasks', $board);

        $data = $request->validate([
            'column_id' => ['required', 'uuid', 'exists:columns,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'type' => ['required', 'string', 'in:incident,maintenance,qa,it,general'],
            'priority' => ['required', 'string', 'in:low,medium,high,critical'],
            'due_at' => ['nullable', 'date'],
            'assignee_id' => ['nullable', 'uuid', 'exists:users,id'],
        ]);

        abort_unless($board->columns()->where('id', $data['column_id'])->exists(), 422);

        $task = Task::create([
            'organization_id' => $board->organization_id,
            'board_id' => $board->id,
            'column_id' => $data['column_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'priority' => $data['priority'],
            'due_at' => $data['due_at'] ?? null,
            'assignee_id' => $data['assignee_id'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        $activityLog->log(
            $board->organization,
            $request->user(),
            'task.created',
            Task::class,
            $task->id,
            null,
            ['title' => $task->title, 'column_id' => $task->column_id],
        );

        return back()->with('status', 'Nowe zadanie zostało utworzone.');
    }
}
