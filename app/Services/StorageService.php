<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;

class StorageService
{
    /**
     * Store a file in the given path.
     *
     * @param UploadedFile $file
     * @param string $path
     * @return string
     */
    public static function store(UploadedFile $file, string $path): string
    {
        $directory = 'uploads/' . ltrim($path, '/') . '/' . Carbon::now()->format('Y/m/d');
        $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());

        // Basic storage logic. If intervention/image was installed, we'd add compression here.
        return $file->storeAs($directory, $fileName, 'public');
    }

    /**
     * Get the public URL of a file.
     *
     * @param string|null $path
     * @return string|null
     */
    public static function url(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
