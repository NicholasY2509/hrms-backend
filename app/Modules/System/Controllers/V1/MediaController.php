<?php

namespace App\Modules\System\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Generate a GCP Signed URL for direct-to-storage uploads.
     */
    public function generateUploadUrl(Request $request)
    {
        $request->validate([
            'filename' => 'required|string',
            'content_type' => 'required|string',
        ]);

        $filename = $request->input('filename');
        $contentType = $request->input('content_type');

        // Clean filename and generate a unique path
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $cleanFilename = Str::slug(pathinfo($filename, PATHINFO_FILENAME));
        $uuid = Str::uuid()->toString();
        
        $path = "temp-uploads/{$uuid}/{$cleanFilename}." . ($extension ?: 'bin');

        $disk = Storage::disk('gcs');

        // Generate a V4 signed URL valid for 15 minutes
        $url = $disk->temporaryUploadUrl(
            $path, 
            now()->addMinutes(15),
            ['ContentType' => $contentType]
        );

        return response()->json([
            'data' => [
                'url' => $url,
                'path' => $path,
            ],
            'message' => 'Upload URL generated successfully',
        ]);
    }
}
