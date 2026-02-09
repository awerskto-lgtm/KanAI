<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Board;
use App\Models\Organization;
use App\Models\Task;
use App\Models\Team;
use App\Services\RbacService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BoardController extends Controller
{
    public function index(Request $request, RbacService $rbac)
    {
        $user = $request->user();

        $boards = Board::query()
            ->whereHas('organization.users', fn ($q) => $q->where('users.id', $user->id))
            ->with(['team', 'organization'])
            ->paginate(10);

        $organizations = Organization::query()
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->with('teams')
            ->get();

        $manageableTeams = Team::query()
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id)->whereIn('team_user.role', ['manager', 'team_lead']))
            ->orWhereHas('organization.users', fn ($q) => $q->where('users.id', $user->id)->where('organization_user.role', 'org_admin'))
            ->with('organization')
            ->get()
            ->unique('id')
            ->values();

        return view('boards.index', compact('boards', 'organizations', 'manageableTeams'));
    }

    public function show(Request $request, Board $board, RbacService $rbac)
    {
        $this->authorize('view', $board);

        $board->load('columns');

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'column_id' => (string) $request->query('column_id', ''),
            'include_archived' => (bool) $request->boolean('include_archived'),
            'mine' => (bool) $request->boolean('mine'),
            'blocked' => (bool) $request->boolean('blocked'),
            'due_soon' => (bool) $request->boolean('due_soon'),
        ];

        $dueBoundary = Carbon::now()->addDays(3);

        $tasksQuery = Task::query()
            ->where('board_id', $board->id)
            ->when(!$filters['include_archived'], fn ($q) => $q->whereNull('archived_at'))
            ->when($filters['column_id'] !== '', fn ($q) => $q->where('column_id', $filters['column_id']))
            ->when($filters['mine'], fn ($q) => $q->where('assignee_id', $request->user()->id))
            ->when($filters['blocked'], fn ($q) => $q->where('is_blocked', true))
            ->when($filters['due_soon'], fn ($q) => $q->whereNotNull('due_at')->where('due_at', '<=', $dueBoundary))
            ->when($filters['q'] !== '', function ($q) use ($filters) {
                $q->where(function ($nested) use ($filters) {
                    $nested->where('title', 'like', "%{$filters['q']}%")
                        ->orWhere('description', 'like', "%{$filters['q']}%");
                });
            })
            ->with(['assignee', 'attachments'])
            ->orderByDesc('updated_at');

        $tasks = $tasksQuery->get();
        $tasksByColumn = $tasks->groupBy('column_id');

        $recentActivity = ActivityLog::query()
            ->where('organization_id', $board->organization_id)
            ->whereIn('subject_id', $tasks->pluck('id')->all())
            ->latest()
            ->limit(12)
            ->get();

        return view('boards.show', [
            'board' => $board,
            'tasksByColumn' => $tasksByColumn,
            'filters' => $filters,
            'shareUrl' => $request->fullUrl(),
            'canManageBoard' => $rbac->canManageBoard($request->user(), $board),
            'teamMembers' => $board->team->users()->orderBy('name')->get(['users.id', 'users.name']),
            'recentActivity' => $recentActivity,
        ]);
    }
}
