<?php

namespace App\Http\Controllers;

use App\Jobs\TranscodeVideoJob;
use App\Models\Listing;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VideoController extends Controller
{
    public function store(Request $req)
    {
        $req->validate([
            'listing_id' => ['required', 'integer', 'exists:listings,id'],
            'title' => 'required|string|max:255',
            'file' => 'required|file|mimetypes:video/mp4,video/quicktime,video/webm|max:204800',
        ]);

        $path = $req->file('file')->store('videos/originals', 'public');
        $publicUrl = Storage::disk('public')->url($path);

        $video = Video::create([
            'listing_id' => $req->listing_id,
            'title' => $req->title,
            'source_url' => $publicUrl,
            'status' => 'UPLOADED',
        ]);

        $video->assets()->create([
            'type' => 'ORIGINAL',
            'url' => $publicUrl,
            'size_bytes' => $req->file('file')->getSize(),
        ]);

        TranscodeVideoJob::dispatch($video->id)->afterCommit();

        return response()->json(['data' => $video], 201);
    }

    public function show(string $id)
    {
        return Video::with('assets', 'listing')->findOrFail($id);
    }

    public function byListing(int $listingId)
    {
        $listing = Listing::findOrFail($listingId);

        return $listing->videos()->with('assets')->get();
    }

    public function index()
    {
        return Listing::with('videos.assets')->get();
    }

    public function transcode(string $id)
    {
        $video = Video::findOrFail($id);
        TranscodeVideoJob::dispatch($video->id)->afterCommit();

        return response()->json([
            'message' => 'Transcode job dispatched.',
            'data' => ['video_id' => $video->id],
        ], 202);
    }

    public function patchStatus(Request $request, string $id)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['UPLOADED', 'PROCESSING', 'READY', 'FAILED'])],
            'error_message' => ['nullable', 'string'],
        ]);

        $video = Video::findOrFail($id);

        $video->status = $validated['status'];
        $video->error_message = $validated['status'] === 'FAILED'
            ? ($validated['error_message'] ?? $video->error_message)
            : null;
        $video->save();

        return response()->json(['data' => $video]);
    }
}
