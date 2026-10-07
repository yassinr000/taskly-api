<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification
{
    public function __construct(public Task $task, public string $assignedBy) {}

    // La notification est enregistrée dans la table "notifications".
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    // Ces données sont stockées en JSON et renvoyées à React.
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'assigned',
            'task_id' => $this->task->id,
            'board_id' => $this->task->board_id,
            'title' => $this->task->title,
            'message' => "{$this->assignedBy} t'a assigné la tâche « {$this->task->title} »",
        ];
    }
}
