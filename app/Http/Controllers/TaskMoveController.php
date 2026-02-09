<?php

namespace App\Http\Controllers;

use App\Models\BoardColumn;
use App\Models\Task;
use App\Services\TaskMoveService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskMoveController extends Controller
{
    public function __invoke(Request $request, Task $task, TaskMoveService $service): RedirectResponse|JsonResponse
    {
        $this->authorize('move', $task);

        $data = $request->validate([
            'to_column_id' => [
                'required',
                'uuid',
                Rule::exists('columns', 'id')->where('board_id', $task->board_id),
            ],
        ]);

        $target = BoardColumn::findOrFail($data['to_column_id']);

        try {
            $movedTask = $service->move($task, $target, $request->user());
        } catch (DomainException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->with('status', 'Nie udało się przenieść zadania: '.$e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Zadanie przeniesione.', 'task_id' => $movedTask->id]);
        }

        return back()->with('status', 'Zadanie przeniesione.');
    }
}
