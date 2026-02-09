<?php

namespace App\Providers;

use App\Models\Board;
use App\Models\Task;
use App\Policies\BoardPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Board::class, BoardPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
    }
}
