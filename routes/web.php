<?php

use App\Http\Controllers\BoardColumnController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\BoardManagementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskArchiveController;
use App\Http\Controllers\TaskAttachmentController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskMoveController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return redirect()->route('boards.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/boards', [BoardController::class, 'index'])->name('boards.index');
    Route::post('/boards', [BoardManagementController::class, 'store'])->name('boards.store');
    Route::get('/boards/{board}', [BoardController::class, 'show'])->name('boards.show');
    Route::post('/boards/{board}/columns', [BoardColumnController::class, 'store'])->name('boards.columns.store');
    Route::post('/boards/{board}/tasks', [TaskController::class, 'store'])->name('boards.tasks.store');

    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');

    Route::post('/tasks/{task}/move', TaskMoveController::class)->name('tasks.move');
    Route::post('/tasks/{task}/archive', [TaskArchiveController::class, 'archive'])->name('tasks.archive');
    Route::post('/tasks/{task}/unarchive', [TaskArchiveController::class, 'unarchive'])->name('tasks.unarchive');
    Route::post('/tasks/{task}/attachments', [TaskAttachmentController::class, 'store'])->name('tasks.attachments.store');
    Route::get('/attachments/{attachment}/download', [TaskAttachmentController::class, 'download'])->name('attachments.download');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
