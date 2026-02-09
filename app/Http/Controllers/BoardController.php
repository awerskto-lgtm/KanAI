<?php

namespace App\Http\Controllers;

use App\Models\Board;
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

    public function show(Board $board)
    {
        $this->authorize('view', $board);

        $board->load(['columns.tasks.assignee']);

        return view('boards.show', compact('board'));
    }
}
