<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = ['task_id', 'user_id', 'path', 'original_name'];

    // 🔒 Le chemin interne du fichier n'est jamais envoyé au navigateur.
    protected $hidden = ['path'];

    public function task() { return $this->belongsTo(Task::class); }
    public function user() { return $this->belongsTo(User::class); }
}
