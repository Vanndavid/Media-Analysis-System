<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->index(['listing_id', 'status', 'created_at'], 'videos_listing_status_created_idx');
        });

        Schema::table('video_events', function (Blueprint $table) {
            $table->index(['video_id', 'event_type', 'created_at'], 'video_events_video_type_created_idx');
            $table->index(['event_type', 'created_at'], 'video_events_type_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('video_events', function (Blueprint $table) {
            $table->dropIndex('video_events_video_type_created_idx');
            $table->dropIndex('video_events_type_created_idx');
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->dropIndex('videos_listing_status_created_idx');
        });
    }
};
