<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function boardWithMember(): array
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $board = Board::factory()->create(['owner_id' => $owner->id]);
        $board->members()->attach([$owner->id, $member->id]);
        return [$owner, $member, $board];
    }

    public function test_assignee_receives_a_notification(): void
    {
        [$owner, $member, $board] = $this->boardWithMember();
        Sanctum::actingAs($owner);

        $this->postJson("/api/boards/{$board->id}/tasks", [
            'title' => 'Préparer la démo',
            'assigned_to' => $member->id,
        ])->assertCreated();

        $this->assertSame(1, $member->unreadNotifications()->count());
    }

    public function test_self_assignment_does_not_notify(): void
    {
        [$owner, , $board] = $this->boardWithMember();
        Sanctum::actingAs($owner);

        $this->postJson("/api/boards/{$board->id}/tasks", [
            'title' => 'Pour moi',
            'assigned_to' => $owner->id,
        ])->assertCreated();

        $this->assertSame(0, $owner->unreadNotifications()->count());
    }

    public function test_user_can_mark_a_notification_as_read(): void
    {
        [$owner, $member, $board] = $this->boardWithMember();
        Sanctum::actingAs($owner);
        $this->postJson("/api/boards/{$board->id}/tasks", [
            'title' => 'Tâche', 'assigned_to' => $member->id,
        ]);

        Sanctum::actingAs($member);
        $id = $member->notifications()->first()->id;
        $this->postJson("/api/notifications/{$id}/read")->assertNoContent();
        $this->getJson('/api/notifications')->assertOk()->assertJsonPath('unread_count', 0);
    }
}
