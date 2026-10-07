<?php
namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Http\Request;

abstract class Controller
{
    protected function requireMember(Request $request, Board $board): void
    {
        abort_unless($board->hasMember($request->user()), 403);
    }

    protected function requireOwner(Request $request, Board $board): void
    {
        abort_unless($board->owner_id === $request->user()->id, 403);
    }
}
