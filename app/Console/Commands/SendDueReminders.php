<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDueSoon;
use Illuminate\Console\Command;

class SendDueReminders extends Command
{
    protected $signature = 'taskly:send-reminders';
    protected $description = "Notifie les assignés dont la tâche est à rendre demain";

    public function handle(): int
    {
        $tasks = Task::whereDate('due_date', today()->addDay())
            ->where('status', '!=', 'done')
            ->whereNotNull('assigned_to')
            ->get();

        foreach ($tasks as $task) {
            User::find($task->assigned_to)?->notify(new TaskDueSoon($task));
        }

        $this->info($tasks->count() . ' rappel(s) envoyé(s).');
        return self::SUCCESS;
    }
}
