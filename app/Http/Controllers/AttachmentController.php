<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function index(Request $request, Task $task)
    {
        $this->requireMember($request, $task->board);
        return $task->attachments()->latest()->get();
    }

    public function store(Request $request, Task $task)
    {
        $this->requireMember($request, $task->board);

        // 🔒 5 Mo maximum et seulement certains types de fichiers.
        $request->validate([
            'file' => 'required|file|max:5120|mimes:pdf,png,jpg,jpeg,gif,txt,doc,docx,xls,xlsx',
        ]);

        $file = $request->file('file');
        // 🔒 Disque "local" : les fichiers sont privés, sans URL publique.
        $path = $file->store('attachments', 'local');

        $attachment = $task->attachments()->create([
            'user_id' => $request->user()->id,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
        ]);

        return response()->json($attachment, 201);
    }

    public function download(Request $request, Attachment $attachment)
    {
        $this->requireMember($request, $attachment->task->board);
        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    }

    public function destroy(Request $request, Attachment $attachment)
    {
        $board = $attachment->task->board;
        $this->requireMember($request, $board);
        abort_unless(
            $attachment->user_id === $request->user()->id || $board->owner_id === $request->user()->id,
            403
        );
        Storage::disk('local')->delete($attachment->path);
        $attachment->delete();
        return response()->noContent();
    }
}
