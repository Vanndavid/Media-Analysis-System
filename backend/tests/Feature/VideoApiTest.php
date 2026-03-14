<?php

namespace Tests\Feature;

use App\Jobs\TranscodeVideoJob;
use App\Models\Listing;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
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
}
