<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AlbumCoverThumbnail
{
    public const int Size = 512;

    private const int Quality = 82;

    /** @var array<string, string|null> */
    private static array $urlCache = [];

    public static function url(?string $cover): ?string
    {
        if ($cover === null || trim($cover) === '') {
            return null;
        }

        if (array_key_exists($cover, self::$urlCache)) {
            return self::$urlCache[$cover];
        }

        $browserPath = self::browserPath($cover);

        return self::$urlCache[$cover] = $browserPath !== null ? asset($browserPath) : null;
    }

    public static function browserPath(?string $cover): ?string
    {
        if ($cover === null || trim($cover) === '') {
            return null;
        }

        if (self::isExternalUrl($cover)) {
            return $cover;
        }

        $browserPath = PublicAudio::browserPath($cover);

        if ($browserPath === null) {
            return null;
        }

        $thumbnail = self::thumbnailBrowserPath($browserPath);

        if ($thumbnail === null) {
            return $browserPath;
        }

        if (! File::exists(public_path($thumbnail))) {
            self::generate($browserPath);
        }

        return File::exists(public_path($thumbnail))
            ? $thumbnail
            : $browserPath;
    }

    public static function generate(?string $cover, bool $force = false): ?string
    {
        if ($cover === null || trim($cover) === '' || self::isExternalUrl($cover)) {
            return null;
        }

        $browserPath = PublicAudio::browserPath($cover);

        if ($browserPath === null) {
            return null;
        }

        $sourcePath = PublicAudio::path($browserPath);

        if ($sourcePath === null || ! File::isFile($sourcePath)) {
            return null;
        }

        $thumbnailBrowserPath = self::thumbnailBrowserPath($browserPath);

        if ($thumbnailBrowserPath === null) {
            return null;
        }

        $thumbnailPath = public_path($thumbnailBrowserPath);

        if (! $force && File::isFile($thumbnailPath) && File::lastModified($thumbnailPath) >= File::lastModified($sourcePath)) {
            return $thumbnailBrowserPath;
        }

        $sourceSize = @getimagesize($sourcePath);

        if ($sourceSize === false) {
            return null;
        }

        [$sourceWidth, $sourceHeight, $imageType] = $sourceSize;
        $sourceImage = self::createImage($sourcePath, $imageType);

        if ($sourceImage === null) {
            return null;
        }

        [$targetWidth, $targetHeight] = self::targetDimensions($sourceWidth, $sourceHeight);
        $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($targetImage === false) {
            imagedestroy($sourceImage);

            return null;
        }

        imagecopyresampled(
            $targetImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight,
        );

        File::ensureDirectoryExists(dirname($thumbnailPath));
        $saved = imagejpeg($targetImage, $thumbnailPath, self::Quality);

        imagedestroy($sourceImage);
        imagedestroy($targetImage);

        return $saved ? $thumbnailBrowserPath : null;
    }

    public static function forget(?string $cover): void
    {
        $thumbnailBrowserPath = self::thumbnailBrowserPath($cover);

        if ($thumbnailBrowserPath !== null) {
            File::delete(public_path($thumbnailBrowserPath));
        }
    }

    private static function thumbnailBrowserPath(?string $cover): ?string
    {
        if ($cover === null || trim($cover) === '' || self::isExternalUrl($cover)) {
            return null;
        }

        $directory = trim(dirname($cover), '.');
        $fileName = pathinfo($cover, PATHINFO_FILENAME);

        if ($fileName === '') {
            return null;
        }

        return trim($directory, '/').'/_thumbnails/'.self::safeFileName($fileName).'-'.self::Size.'.jpg';
    }

    /**
     * @return array{0:int, 1:int}
     */
    private static function targetDimensions(int $sourceWidth, int $sourceHeight): array
    {
        $largestSide = max($sourceWidth, $sourceHeight);

        if ($largestSide <= self::Size) {
            return [$sourceWidth, $sourceHeight];
        }

        $ratio = self::Size / $largestSide;

        return [
            max(1, (int) round($sourceWidth * $ratio)),
            max(1, (int) round($sourceHeight * $ratio)),
        ];
    }

    private static function createImage(string $path, int $imageType): mixed
    {
        return match ($imageType) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            IMAGETYPE_AVIF => function_exists('imagecreatefromavif') ? @imagecreatefromavif($path) : null,
            default => null,
        };
    }

    private static function safeFileName(string $fileName): string
    {
        $safeName = Str::of($fileName)
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9]+/', '_')
            ->trim('_')
            ->toString();

        return $safeName !== '' ? $safeName : (string) Str::ulid();
    }

    private static function isExternalUrl(string $path): bool
    {
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
    }
}
