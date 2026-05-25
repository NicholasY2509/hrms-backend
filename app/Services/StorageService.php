<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

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

        try {
            $mimeType = $file->getMimeType();

            if (str_starts_with($mimeType, 'image/') && !in_array($mimeType, ['image/svg+xml', 'image/gif'])) {
                $manager = new ImageManager(new Driver());
                $image = $manager->read($file->getRealPath());

                $image->scaleDown(width: 1920);

                $encoded = $image->toJpeg(75);

                $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '.jpg';
                $fullPath = $directory . '/' . $fileName;

                Storage::put($fullPath, (string) $encoded);

                return $fullPath;
            }
        } catch (\Throwable $e) {
            Log::warning('Image compression failed: ' . $e->getMessage());
        }

        return $file->storeAs($directory, $fileName);
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

        // Prioritize GCS temporary URLs if configured
        $gcsConfig = config('filesystems.disks.gcs');
        if (!empty($gcsConfig['bucket']) && ($gcsConfig['driver'] ?? '') === 'gcs') {
            $keyPath = $gcsConfig['keyFilePath'] ?? $gcsConfig['keyFile'] ?? null;
            
            try {
                // Only attempt if key is an array (decoded JSON) or a valid existing file path
                if (is_array($keyPath) || (is_string($keyPath) && file_exists($keyPath))) {
                    $cacheKey = 'gcs_temp_url_' . md5($path);
                    return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(55), function () use ($path) {
                        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                        $disk = Storage::disk('gcs');
                        return $disk->temporaryUrl($path, now()->addMinutes(60));
                    });
                }
            } catch (\Throwable $e) {
                // Fallback to default behavior if GCS fails
                Log::warning('GCS temporary URL generation failed: ' . $e->getMessage());
            }
        }

        return Storage::url($path);
    }
}
