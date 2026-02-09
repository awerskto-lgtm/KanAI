<?php

namespace App\Services;

use App\Models\BoardColumn;
use App\Models\Task;
use App\Models\TaskColumnEvent;
use App\Models\User;
use Carbon\CarbonImmutable;
use DomainException;

class TaskMoveService
{
    public function __construct(
        private readonly RbacService $rbac,
        private readonly ActivityLogService $activityLog,
    ) {}

    public function move(Task $task, BoardColumn $targetColumn, User $actor): Task
    {
        if ($task->board_id !== $targetColumn->board_id) {
            throw new DomainException('Target column must belong to same board.');
        }

        $openDependencies = $task->dependencies()->whereHas('column', fn ($q) => $q->where('type', '!=', 'done'))->count();
        if ($targetColumn->type === 'done' && $openDependencies > 0) {
            throw new DomainException('Task has unfinished dependencies.');
        }

        if ($targetColumn->wip_limit !== null) {
            $current = Task::where('column_id', $targetColumn->id)->count();
            $canOverride = $this->rbac->canManageBoard($actor, $task->board);
            if ($current >= $targetColumn->wip_limit && !$canOverride) {
                throw new DomainException('WIP limit exceeded.');
            }
        }

        $fromColumnId = $task->column_id;
        $task->update(['column_id' => $targetColumn->id]);

        TaskColumnEvent::create([
            'task_id' => $task->id,
            'from_column_id' => $fromColumnId,
            'to_column_id' => $targetColumn->id,
            'moved_by' => $actor->id,
            'moved_at' => CarbonImmutable::now(),
        ]);

        $this->activityLog->log(
            $task->board->organization,
            $actor,
            'task.moved',
            Task::class,
            $task->id,
            ['column_id' => $fromColumnId],
            ['column_id' => $targetColumn->id],
        );

        return $task->fresh();
    }
}
