<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'organization_id','board_id','column_id','title','description','type','priority','tags','due_at',
        'created_by','assignee_id','reviewer_id','is_blocked','blocked_reason','blocked_at',
    ];

    protected function casts(): array
    {
        return ['tags' => 'array', 'due_at' => 'datetime', 'blocked_at' => 'datetime', 'is_blocked' => 'boolean'];
    }

    public function board(): BelongsTo { return $this->belongsTo(Board::class); }
    public function column(): BelongsTo { return $this->belongsTo(BoardColumn::class, 'column_id'); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assignee_id'); }
    public function events(): HasMany { return $this->hasMany(TaskColumnEvent::class); }

    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_on_task_id');
    }
}
