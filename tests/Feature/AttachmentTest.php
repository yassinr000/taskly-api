<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Board;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttachmentTest extends TestCase
{
    use RefreshDatabase;

    private function taskWithOwner(): array
    {
        $owner = User::factory()->create();
        $board = Board::factory()->create(['owner_id' => $owner->id]);
        $board->members()->attach($owner->id);
        $task = Task::factory()->create(['board_id' => $board->id, 'created_by' => $owner->id]);
        return [$owner, $task];
    }

    public function test_member_can_upload_a_pdf(): void
    {
        Storage::fake('local');
        [$owner, $task] = $this->taskWithOwner();
        Sanctum::actingAs($owner);

        $file = UploadedFile::fake()->create('rapport.pdf', 100, 'application/pdf');
        $this->postJson("/api/tasks/{$task->id}/attachments", ['file' => $file])->assertCreated();

        Storage::disk('local')->assertExists(Attachment::first()->path);
    }

    public function test_dangerous_file_types_are_rejected(): void
    {
        Storage::fake('local');
        [$owner, $task] = $this->taskWithOwner();
        Sanctum::actingAs($owner);

        $file = UploadedFile::fake()->create('virus.php', 10);
        $this->postJson("/api/tasks/{$task->id}/attachments", ['file' => $file])->assertUnprocessable();
    }

    public function test_non_member_cannot_download(): void
    {
        Storage::fake('local');
        [$owner, $task] = $this->taskWithOwner();
        Sanctum::actingAs($owner);
        $file = UploadedFile::fake()->create('rapport.pdf', 100, 'application/pdf');
        $this->postJson("/api/tasks/{$task->id}/attachments", ['file' => $file]);

        Sanctum::actingAs(User::factory()->create());
        $this->getJson('/api/attachments/' . Attachment::first()->id . '/download')->assertForbidden();
    }
}
