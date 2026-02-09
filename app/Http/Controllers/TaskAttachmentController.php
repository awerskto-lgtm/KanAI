<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentController extends Controller
{
    public function store(Request $request, Task $task, ActivityLogService $activityLog): RedirectResponse
    {
        $this->authorize('update', $task);

        $data = $request->validate([
            'attachment' => ['required', 'file', 'max:15360', 'mimes:pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png,webp,txt,md'],
        ]);

        $file = $data['attachment'];
        $path = $file->store('task-attachments/'.$task->id, 'private');

        $attachment = TaskAttachment::create([
            'task_id' => $task->id,
            'uploaded_by' => $request->user()->id,
            'file_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize() ?: 0,
        ]);

        $activityLog->log(
            $task->board->organization,
            $request->user(),
            'task.attachment_uploaded',
            Task::class,
            $task->id,
            null,
            ['attachment_id' => $attachment->id, 'file_name' => $attachment->file_name],
        );

        return back()->with('status', 'Plik został dodany do zadania.');
    }

    public function download(TaskAttachment $attachment)
    {
        $task = $attachment->task;
        $this->authorize('view', $task);

        return Storage::disk('private')->download($attachment->path, $attachment->file_name);
    }
}
