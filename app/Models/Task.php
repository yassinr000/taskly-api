<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['board_id', 'created_by', 'assigned_to', 'title',
                           'description', 'status', 'priority', 'due_date'];
    protected $casts = ['due_date' => 'date:Y-m-d'];

    public function board()          { return $this->belongsTo(Board::class); }
    public function assignee()       { return $this->belongsTo(User::class, 'assigned_to'); }
    public function notes()          { return $this->hasMany(Note::class); }
    public function attachments()    { return $this->hasMany(Attachment::class); }
    public function checklistItems() { return $this->hasMany(ChecklistItem::class); }

    // Supprime les fichiers du disque (la base, elle, se vide toute seule en cascade).
    public function deleteFiles(): void
    {
        foreach ($this->attachments as $attachment) {
            Storage::disk('local')->delete($attachment->path);
        }
    }
}
