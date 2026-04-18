<?php

namespace Tests\Feature;

use App\Jobs\TranscodeVideoJob;
use App\Jobs\RecordVideoEventJob;
use App\Models\Listing;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use App\Models\VideoEvent;
use Tests\TestCase;

class VideoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_video(): void
    {
        $listing = Listing::factory()->create();
        $fakeFile = UploadedFile::fake()->create('demo.mp4', 1000, 'video/mp4');

        $response = $this->postJson('/api/videos', [
            'listing_id' => $listing->id,
            'title' => 'Kitchen Tour',
            'file' => $fakeFile,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'UPLOADED');
    }

    public function test_create_video_requires_existing_listing(): void
    {
        $fakeFile = UploadedFile::fake()->create('demo.mp4', 1000, 'video/mp4');

        $response = $this->postJson('/api/videos', [
            'listing_id' => 999999,
            'title' => 'Kitchen Tour',
            'file' => $fakeFile,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['listing_id']);
    }

    public function test_manual_transcode_endpoint_dispatches_job(): void
    {
        Queue::fake();

        $listing = Listing::factory()->create();
        $video = Video::create([
            'listing_id' => $listing->id,
            'title' => 'Kitchen Tour',
            'source_url' => '/storage/videos/originals/demo.mp4',
            'status' => 'UPLOADED',
        ]);

        $response = $this->postJson("/api/videos/{$video->id}/transcode");

        $response->assertAccepted()
            ->assertJsonPath('data.video_id', $video->id);

        Queue::assertPushed(TranscodeVideoJob::class);
    }

    public function test_video_event_endpoint_dispatches_async_job(): void
    {
        Queue::fake();

        $listing = Listing::factory()->create();
        $video = Video::create([
            'listing_id' => $listing->id,
            'title' => 'Kitchen Tour',
            'source_url' => '/storage/videos/originals/demo.mp4',
            'status' => 'READY',
        ]);

        $response = $this->postJson("/api/videos/{$video->id}/events", [
            'event_type' => 'PLAY',
        ]);

        $response->assertStatus(202)
            ->assertJsonPath('ok', true);

        Queue::assertPushed(RecordVideoEventJob::class);
    }

    public function test_top_videos_returns_aggregated_video_details(): void
    {
        $listing = Listing::factory()->create([
            'title' => 'Listing A',
            'address' => '123 Main St',
        ]);

        $video = Video::create([
            'listing_id' => $listing->id,
            'title' => 'Kitchen Tour',
            'source_url' => '/storage/videos/originals/demo.mp4',
            'status' => 'READY',
        ]);

        VideoEvent::create([
            'video_id' => $video->id,
            'event_type' => 'PLAY',
        ]);

        $response = $this->getJson('/api/analytics/top-videos');

        $response->assertOk()
            ->assertJsonPath('0.video_id', $video->id)
            ->assertJsonPath('0.title', 'Kitchen Tour')
            ->assertJsonPath('0.listing_title', 'Listing A')
            ->assertJsonPath('0.plays', 1);
    }
}
