<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Task;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function index(Request $request)
    {
        $boards = Board::query()
            ->whereHas('organization.users', fn ($q) => $q->where('users.id', $request->user()->id))
            ->with(['team', 'organization'])
            ->paginate(10);

        return view('boards.index', compact('boards'));
    }

    public function show(Request $request, Board $board)
    {
        $this->authorize('view', $board);

        $board->load('columns');

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'column_id' => (string) $request->query('column_id', ''),
            'include_archived' => (bool) $request->boolean('include_archived'),
        ];

        $tasksQuery = Task::query()
            ->where('board_id', $board->id)
            ->when(!$filters['include_archived'], fn ($q) => $q->whereNull('archived_at'))
            ->when($filters['column_id'] !== '', fn ($q) => $q->where('column_id', $filters['column_id']))
            ->when($filters['q'] !== '', function ($q) use ($filters) {
                $q->where(function ($nested) use ($filters) {
                    $nested->where('title', 'like', "%{$filters['q']}%")
                        ->orWhere('description', 'like', "%{$filters['q']}%");
                });
            })
            ->with(['assignee', 'attachments'])
            ->orderByDesc('updated_at');

        $tasksByColumn = $tasksQuery->get()->groupBy('column_id');

        return view('boards.show', [
            'board' => $board,
            'tasksByColumn' => $tasksByColumn,
            'filters' => $filters,
            'shareUrl' => $request->fullUrl(),
        ]);
    }
}
