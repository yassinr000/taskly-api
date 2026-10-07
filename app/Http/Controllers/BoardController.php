<?php
namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\User;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->boards()->withCount('tasks')->with('owner:id,name')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);
        $board = Board::create($data + ['owner_id' => $request->user()->id]);
        $board->members()->attach($request->user()->id);
        return response()->json($board, 201);
    }

    public function show(Request $request, Board $board)
    {
        $this->requireMember($request, $board);
        return $board->load('owner:id,name', 'members:id,name,email');
    }

    public function update(Request $request, Board $board)
    {
        $this->requireOwner($request, $board);
        $board->update($request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]));
        return $board;
    }

    public function destroy(Request $request, Board $board)
    {
        $this->requireOwner($request, $board);
        $board->tasks()->with('attachments')->get()->each->deleteFiles();
        $board->delete();
        return response()->noContent();
    }

    public function addMember(Request $request, Board $board)
    {
        $this->requireOwner($request, $board);
        $email = $request->validate(['email' => 'required|email'])['email'];
        $user = User::where('email', $email)->first();
        if ($user) {
            $board->members()->syncWithoutDetaching($user->id);
        }
        // 🔒 Même réponse si l'email n'existe pas : on ne révèle pas qui a un compte.
        return response()->json(['message' => 'Si cet email correspond à un compte, il a été ajouté au board.']);
    }

    public function removeMember(Request $request, Board $board, User $user)
    {
        $isOwner = $board->owner_id === $request->user()->id;
        abort_unless($isOwner || $user->id === $request->user()->id, 403);
        abort_if($user->id === $board->owner_id, 422, 'Le propriétaire ne peut pas quitter son board.');
        $board->members()->detach($user->id);
        $board->tasks()->where('assigned_to', $user->id)->update(['assigned_to' => null]);
        return response()->noContent();
    }
}
