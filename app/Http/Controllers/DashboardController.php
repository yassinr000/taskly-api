<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $mine = Task::where('assigned_to', $request->user()->id);

        return [
            'my_tasks'    => (clone $mine)->where('status', '!=', 'done')->count(),
            'todo'        => (clone $mine)->where('status', 'todo')->count(),
            'in_progress' => (clone $mine)->where('status', 'in_progress')->count(),
            'completed'   => (clone $mine)->where('status', 'done')->count(),
            // Les tâches en retard sont déjà dans todo / in_progress : ne jamais les ajouter au donut.
            'overdue'     => (clone $mine)->where('status', '!=', 'done')
                                ->whereDate('due_date', '<', today())->count(),
            'upcoming'    => (clone $mine)->where('status', '!=', 'done')
                                ->whereDate('due_date', '>=', today())
                                ->orderBy('due_date')->limit(5)->get(['id', 'title', 'due_date']),
        ];
    }
}
