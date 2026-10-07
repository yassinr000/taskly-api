<?php

namespace Database\Factories;

use App\Models\Board;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'board_id' => Board::factory(),
            'created_by' => User::factory(),
            'title' => fake()->sentence(3),
            'status' => 'todo',
            'priority' => 'medium',
            'due_date' => now()->addDays(3)->toDateString(),
        ];
    }
}
