<?php
namespace Tests\Feature;

use App\Models\Board;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private function boardWithOwner(): array
    {
        $owner = User::factory()->create();
        $board = Board::factory()->create(['owner_id' => $owner->id]);
        $board->members()->attach($owner->id);
        return [$owner, $board];
    }

    public function test_non_member_cannot_list_tasks(): void
    {
        [, $board] = $this->boardWithOwner();
        Sanctum::actingAs(User::factory()->create());
        $this->getJson("/api/boards/{$board->id}/tasks")->assertForbidden();
    }

    public function test_cannot_assign_a_task_to_a_non_member(): void
    {
        [$owner, $board] = $this->boardWithOwner();
        $stranger = User::factory()->create();
        Sanctum::actingAs($owner);
        $this->postJson("/api/boards/{$board->id}/tasks", [
            'title' => 'Test', 'assigned_to' => $stranger->id,
        ])->assertUnprocessable();
    }

    public function test_member_can_change_the_status(): void
    {
        [$owner, $board] = $this->boardWithOwner();
        $task = Task::factory()->create(['board_id' => $board->id, 'created_by' => $owner->id]);
        Sanctum::actingAs($owner);
        $this->putJson("/api/tasks/{$task->id}", ['status' => 'done'])
             ->assertOk()->assertJsonPath('status', 'done');
    }

    public function test_only_creator_or_owner_can_delete(): void
    {
        [$owner, $board] = $this->boardWithOwner();
        $member = User::factory()->create();
        $board->members()->attach($member->id);
        $task = Task::factory()->create(['board_id' => $board->id, 'created_by' => $owner->id]);
        Sanctum::actingAs($member);
        $this->deleteJson("/api/tasks/{$task->id}")->assertForbidden();
    }
}
