<?php

namespace App\Actions;

use App\Models\Merchandise;
use App\Models\MerchandiseImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessMerchandiseImage
{
    /**
     * Store an uploaded merchandise image, converting it to WebP.
     *
     * @param  array{quality?: int, is_cover?: bool, sort_order?: int}  $options
     */
    public function handle(Merchandise $item, UploadedFile $file, array $options = []): MerchandiseImage
    {
        $quality = $options['quality'] ?? 82;
        $isCover = $options['is_cover'] ?? false;
        $sortOrder = $options['sort_order'] ?? 0;

        $webpData = $this->convertToWebp($file->getRealPath(), $quality);

        $filename = Str::ulid().'.webp';
        $storagePath = 'merchandise/'.$item->id.'/'.$filename;

        Storage::disk('public')->put($storagePath, $webpData);

        return MerchandiseImage::create([
            'merchandise_id' => $item->id,
            'file_path' => $storagePath,
            'is_cover' => $isCover,
            'sort_order' => $sortOrder,
        ]);
    }

    private function convertToWebp(string $sourcePath, int $quality): string
    {
        if (! function_exists('imagewebp')) {
            return (string) file_get_contents($sourcePath);
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
            return (string) file_get_contents($sourcePath);
        }

        ob_start();
        imagewebp($image, null, $quality);
        $data = ob_get_clean();
        imagedestroy($image);

        return $data ?: (string) file_get_contents($sourcePath);
    }

    /**
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
