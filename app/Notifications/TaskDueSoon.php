<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Notifications\Notification;

class TaskDueSoon extends Notification
{
    public function __construct(public Task $task) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'due_soon',
            'task_id' => $this->task->id,
            'board_id' => $this->task->board_id,
            'title' => $this->task->title,
            'message' => "Rappel : la tâche « {$this->task->title} » est à rendre demain.",
        ];
    }
}
