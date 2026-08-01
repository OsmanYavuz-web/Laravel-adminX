<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function index()
    {
        $disk = Storage::disk('local');
        $files = $disk->exists('media') ? $disk->files('media') : [];
        $media = [];

        foreach ($files as $file) {
            $media[] = [
                'filename' => basename($file),
                'path' => $file,
                'url' => route('media.file', ['filename' => basename($file)]),
                'size' => $disk->size($file),
                'last_modified' => $disk->lastModified($file),
            ];
        }

        return response()->json(['data' => $media]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $filename = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)).'_'.time().'.'.$file->getClientOriginalExtension();
        $file->storeAs('media', $filename, 'local');

        ActivityLog::record(
            event: 'media_uploaded',
            description: "API ile dosya yüklendi: {$filename}",
            user: $request->user()
        );

        return response()->json(['message' => 'File uploaded.', 'filename' => $filename], 201);
    }

    public function delete(Request $request, string $filename)
    {
        $path = "media/{$filename}";
        $disk = Storage::disk('local');

        if (! $disk->exists($path)) {
            return response()->json(['message' => 'File not found.'], 404);
        }

        $disk->delete($path);

        ActivityLog::record(
            event: 'media_deleted',
            description: "API ile dosya silindi: {$filename}",
            user: $request->user()
        );

        return response()->json(['message' => 'File deleted.']);
    }
}
