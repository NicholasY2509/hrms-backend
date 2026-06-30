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
     * Check if GCS is properly configured.
     *
     * @return bool
     */
    public static function isGcsConfigured(): bool
    {
        $gcsConfig = config('filesystems.disks.gcs');
        if (empty($gcsConfig['bucket']) || ($gcsConfig['driver'] ?? '') !== 'gcs') {
            return false;
        }

        $keyPath = $gcsConfig['keyFilePath'] ?? $gcsConfig['keyFile'] ?? null;
        return is_array($keyPath) || (is_string($keyPath) && file_exists($keyPath));
    }

    /**
     * Get the storage disk to use, with fallback to 'public' if GCS is missing.
     *
     * @return string
     */
    public static function getDisk(): string
    {
        $defaultDisk = config('filesystems.default', 'public');
        
        if ($defaultDisk === 'gcs' && !self::isGcsConfigured()) {
            return 'public';
        }
        
        return $defaultDisk;
    }

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

                Storage::disk(self::getDisk())->put($fullPath, (string) $encoded);

                return $fullPath;
            }
        } catch (\Throwable $e) {
            Log::warning('Image compression failed: ' . $e->getMessage());
        }

        return $file->storeAs($directory, $fileName, ['disk' => self::getDisk()]);
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
        if (self::isGcsConfigured()) {
            try {
                $cacheKey = 'gcs_temp_url_' . md5($path);
                return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(55), function () use ($path) {
                    /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                    $disk = Storage::disk('gcs');
                    return $disk->temporaryUrl($path, now()->addMinutes(60));
                });
            } catch (\Throwable $e) {
                // Fallback to default behavior if GCS fails
                Log::warning('GCS temporary URL generation failed: ' . $e->getMessage());
            }
        }

        return Storage::disk(self::getDisk())->url($path);
    }
}
