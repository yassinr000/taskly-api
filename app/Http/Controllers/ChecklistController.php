<?php

namespace App\Http\Controllers;

use App\Models\ChecklistItem;
use App\Models\Task;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    public function index(Request $request, Task $task)
    {
        $this->requireMember($request, $task->board);
        return $task->checklistItems()->orderBy('id')->get();
    }

    public function store(Request $request, Task $task)
    {
        $this->requireMember($request, $task->board);
        $data = $request->validate(['label' => 'required|string|max:255']);
        return response()->json($task->checklistItems()->create($data), 201);
    }

    public function update(Request $request, ChecklistItem $item)
    {
        $this->requireMember($request, $item->task->board);
        $item->update($request->validate([
            'label' => 'sometimes|string|max:255',
            'done' => 'sometimes|boolean',
        ]));
        return $item;
    }

    public function destroy(Request $request, ChecklistItem $item)
    {
        $this->requireMember($request, $item->task->board);
        $item->delete();
        return response()->noContent();
    }
}
