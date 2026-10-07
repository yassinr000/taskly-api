<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'owner_id'];

    public function owner()   { return $this->belongsTo(User::class, 'owner_id'); }
    public function members() { return $this->belongsToMany(User::class); }
    public function tasks()   { return $this->hasMany(Task::class); }

    public function hasMember(User $user): bool
    {
        return $this->members()->where('users.id', $user->id)->exists();
    }
}
