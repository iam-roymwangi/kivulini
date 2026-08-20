<?php

namespace App\Actions;

use App\Models\Event;
use App\Models\EventMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessEventMedia
{
    /**
     * Process an uploaded image file: convert to WebP, compress, store, and
     * create an EventMedia record.
     *
     * @param  array{quality?: int, is_featured?: bool, sort_order?: int, is_post?: bool}  $options
     */
    public function handle(Event $event, UploadedFile $file, array $options = []): EventMedia
    {
        $quality = $options['quality'] ?? 82;
        $isFeatured = $options['is_featured'] ?? false;
        $sortOrder = $options['sort_order'] ?? 0;

        $webpData = $this->convertToWebp($file->getRealPath(), $quality);

        $filename = Str::ulid().'.webp';
        $storagePath = 'events/'.$event->id.'/'.$filename;

        Storage::disk('public')->put($storagePath, $webpData);

        return EventMedia::create([
            'event_id' => $event->id,
            'file_path' => $storagePath,
            'type' => 'image',
            'is_featured' => $isFeatured,
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * Convert an image file to WebP and return the binary string.
     * Falls back to the original file if GD is unavailable or the format
     * is not supported.
     */
    private function convertToWebp(string $sourcePath, int $quality): string
    {
        if (! function_exists('imagewebp')) {
            return file_get_contents($sourcePath);
        }

        $mime = mime_content_type($sourcePath);

        $image = match ($mime) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($sourcePath),
            'image/png' => $this->createFromPng($sourcePath),
            'image/gif' => imagecreatefromgif($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default => null,
        };

        if ($image === null || $image === false) {
            return file_get_contents($sourcePath);
        }

        ob_start();
        imagewebp($image, null, $quality);
        $data = ob_get_clean();
        imagedestroy($image);

        return $data ?: file_get_contents($sourcePath);
    }

    /**
     * Create a GD image from a PNG, preserving transparency.
     *
     * @return \GdImage|false
     */
    private function createFromPng(string $path): mixed
    {
        $image = imagecreatefrompng($path);

        if ($image === false) {
            return false;
        }

        imagealphablending($image, true);
        imagesavealpha($image, true);

        return $image;
    }
}
