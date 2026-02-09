<?php

namespace App\Http\Controllers;

use App\Models\BoardColumn;
use App\Models\Task;
use App\Services\TaskMoveService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskMoveController extends Controller
{
    public function __invoke(Request $request, Task $task, TaskMoveService $service): RedirectResponse
    {
        $this->authorize('move', $task);

        $data = $request->validate([
            'to_column_id' => ['required', 'uuid', 'exists:columns,id'],
        ]);

        $target = BoardColumn::findOrFail($data['to_column_id']);

        try {
            $service->move($task, $target, $request->user());
        } catch (DomainException $e) {
            return back()->with('status', 'Nie udało się przenieść zadania: '.$e->getMessage());
        }

        return back()->with('status', 'Zadanie przeniesione.');
    }
}
