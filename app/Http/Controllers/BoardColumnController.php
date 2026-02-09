<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardColumn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BoardColumnController extends Controller
{
    public function store(Request $request, Board $board): RedirectResponse
    {
        $this->authorize('update', $board);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'type' => ['required', 'string', 'max:40'],
            'wip_limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $nextPosition = ((int) $board->columns()->max('position')) + 1;

        BoardColumn::create([
            'board_id' => $board->id,
            'name' => $data['name'],
            'type' => $data['type'],
            'position' => $nextPosition,
            'wip_limit' => $data['wip_limit'] ?? null,
        ]);

        return back()->with('status', 'Dodano stan pośredni/kolumnę.');
    }
}
