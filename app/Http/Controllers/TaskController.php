<?php
namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    private function rules(Board $board, bool $partial = false): array
    {
        return [
            'title' => ($partial ? 'sometimes|' : 'required|') . 'string|max:255',
            'description' => 'nullable|string|max:5000',
            'status' => ($partial ? 'sometimes|' : '') . 'in:todo,in_progress,done',
            'priority' => ($partial ? 'sometimes|' : '') . 'in:low,medium,high',
            'due_date' => 'nullable|date',
            // 🔒 l'assigné doit être membre de CE board
            'assigned_to' => ['nullable', Rule::exists('board_user', 'user_id')->where('board_id', $board->id)],
        ];
    }

    // Notifie l'assigné, sauf s'il s'est assigné lui-même ou s'il l'était déjà.
    private function notifyAssignee(Task $task, User $by, $previousAssignee = null): void
    {
        $id = (int) $task->assigned_to;
        if ($id && $id !== (int) $previousAssignee && $id !== $by->id) {
            User::find($id)?->notify(new TaskAssigned($task, $by->name));
        }
    }

    public function index(Request $request, Board $board)
    {
        $this->requireMember($request, $board);
        return $board->tasks()->with('assignee:id,name')->latest()->get();
    }

    public function store(Request $request, Board $board)
    {
        $this->requireMember($request, $board);
        $data = $request->validate($this->rules($board));
        $task = $board->tasks()->create($data + ['created_by' => $request->user()->id]);
        $this->notifyAssignee($task, $request->user());
        return response()->json($task->load('assignee:id,name'), 201);
    }

    public function update(Request $request, Task $task)
    {
        $this->requireMember($request, $task->board);
        $previousAssignee = $task->assigned_to;
        $task->update($request->validate($this->rules($task->board, true)));
        $this->notifyAssignee($task, $request->user(), $previousAssignee);
        return $task->load('assignee:id,name');
    }

    public function destroy(Request $request, Task $task)
    {
        $board = $task->board;
        $this->requireMember($request, $board);
        abort_unless(
            $task->created_by === $request->user()->id || $board->owner_id === $request->user()->id,
            403
        );
        $task->deleteFiles();
        $task->delete();
        return response()->noContent();
    }

    public function mine(Request $request)
    {
        return Task::where('assigned_to', $request->user()->id)
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->boolean('overdue'), fn ($q) => $q
                ->where('status', '!=', 'done')->whereDate('due_date', '<', today()))
            ->with('board:id,name')
            ->orderBy('due_date')
            ->get();
    }
}
