<?php

namespace App\Jobs;

use App\Models\VideoEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecordVideoEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $queue = 'events';

    public function __construct(
        public readonly string $videoId,
        public readonly string $eventType,
        public readonly ?string $userAgent,
        public readonly ?string $ip
    ) {
    }

    public function handle(): void
    {
        VideoEvent::create([
            'video_id' => $this->videoId,
            'event_type' => $this->eventType,
            'user_agent' => $this->userAgent,
            'ip' => $this->ip,
        ]);
    }
}
