<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Task;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request, Task $task)
    {
        $this->requireMember($request, $task->board);
        return $task->notes()->with('user:id,name')->latest()->get();
    }

    public function store(Request $request, Task $task)
    {
        $this->requireMember($request, $task->board);
        $data = $request->validate(['content' => 'required|string|max:2000']);
        $note = $task->notes()->create($data + ['user_id' => $request->user()->id]);
        return response()->json($note->load('user:id,name'), 201);
    }

    public function destroy(Request $request, Note $note)
    {
        $board = $note->task->board;
        $this->requireMember($request, $board);
        // Seul l'auteur ou le propriétaire du board peut supprimer.
        abort_unless(
            $note->user_id === $request->user()->id || $board->owner_id === $request->user()->id,
            403
        );
        $note->delete();
        return response()->noContent();
    }
}
