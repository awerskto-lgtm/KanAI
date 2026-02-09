<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('organization_user', function (Blueprint $table) {
            $table->uuid('organization_id');
            $table->uuid('user_id');
            $table->string('role')->default('member');
            $table->timestamps();
            $table->primary(['organization_id', 'user_id']);
            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('name');
            $table->timestamps();
            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->index(['organization_id', 'name']);
        });

        Schema::create('team_user', function (Blueprint $table) {
            $table->uuid('team_id');
            $table->uuid('user_id');
            $table->string('role')->default('viewer');
            $table->timestamps();
            $table->primary(['team_id', 'user_id']);
            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('boards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('team_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            $table->index(['organization_id', 'team_id']);
        });

        Schema::create('board_members', function (Blueprint $table) {
            $table->uuid('board_id');
            $table->uuid('user_id');
            $table->string('role')->nullable();
            $table->timestamps();
            $table->primary(['board_id', 'user_id']);
            $table->foreign('board_id')->references('id')->on('boards')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('columns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('board_id');
            $table->string('name');
            $table->string('type')->default('backlog');
            $table->unsignedInteger('position')->default(0);
            $table->unsignedInteger('wip_limit')->nullable();
            $table->timestamps();
            $table->foreign('board_id')->references('id')->on('boards')->cascadeOnDelete();
            $table->unique(['board_id', 'position']);
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('board_id');
            $table->uuid('column_id');
            $table->string('title');
            $table->longText('description')->nullable();
            $table->string('type')->default('general');
            $table->string('priority')->default('medium');
            $table->json('tags')->nullable();
            $table->timestamp('due_at')->nullable()->index();
            $table->uuid('created_by');
            $table->uuid('assignee_id')->nullable();
            $table->uuid('reviewer_id')->nullable();
            $table->boolean('is_blocked')->default(false);
            $table->text('blocked_reason')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->timestamps();
            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->foreign('board_id')->references('id')->on('boards')->cascadeOnDelete();
            $table->foreign('column_id')->references('id')->on('columns')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('assignee_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('reviewer_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['organization_id', 'board_id', 'column_id']);
        });

        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->uuid('task_id');
            $table->uuid('depends_on_task_id');
            $table->timestamps();
            $table->primary(['task_id', 'depends_on_task_id']);
            $table->foreign('task_id')->references('id')->on('tasks')->cascadeOnDelete();
            $table->foreign('depends_on_task_id')->references('id')->on('tasks')->cascadeOnDelete();
        });

        Schema::create('task_column_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('task_id');
            $table->uuid('from_column_id')->nullable();
            $table->uuid('to_column_id');
            $table->uuid('moved_by');
            $table->timestamp('moved_at');
            $table->timestamps();
            $table->foreign('task_id')->references('id')->on('tasks')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_column_events');
        Schema::dropIfExists('task_dependencies');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('columns');
        Schema::dropIfExists('board_members');
        Schema::dropIfExists('boards');
        Schema::dropIfExists('team_user');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('organization_user');
        Schema::dropIfExists('organizations');
    }
};
