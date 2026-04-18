<?php

namespace App\Http\Controllers;

use App\Http\Requests\VideoEventRequest;
use App\Jobs\RecordVideoEventJob;
use App\Models\Video;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function store(VideoEventRequest $request, string $id)
    {
        $video = Video::findOrFail($id);
        $validated = $request->validated();

        RecordVideoEventJob::dispatch(
            $video->id,
            $validated['event_type'],
            $request->userAgent(),
            $request->ip()
        )->afterCommit();

        return response()->json(['ok' => true], 202);
    }

    public function topVideos()
    {
        $rows = DB::table('video_events as ve')
            ->join('videos as v', 'v.id', '=', 've.video_id')
            ->leftJoin('listings as l', 'l.id', '=', 'v.listing_id')
            ->where('ve.event_type', 'PLAY')
            ->select([
                've.video_id',
                DB::raw('count(*) as plays'),
                'v.title',
                'v.source_url',
                'v.status',
                'v.thumbnail_url',
                'l.title as listing_title',
                'l.address as listing_address',
            ])
            ->groupBy([
                've.video_id',
                'v.title',
                'v.source_url',
                'v.status',
                'v.thumbnail_url',
                'l.title',
                'l.address',
            ])
            ->orderByDesc('plays')
            ->limit(5)
            ->get();

        return response()->json($rows);
    }

    public function health()
    {
        return response()->json(['ok' => true]);
    }
}
