<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskColumnEvent extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['task_id', 'from_column_id', 'to_column_id', 'moved_by', 'moved_at'];

    protected function casts(): array
    {
        return ['moved_at' => 'datetime'];
    }
}
