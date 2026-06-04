<?php

namespace App\Support;

use Illuminate\Support\Str;

class PublicAudio
{
    public static function path(?string $path): ?string
    {
        $normalizedPath = self::normalize($path);

        if ($normalizedPath === null || self::isExternalUrl($normalizedPath)) {
            return $normalizedPath;
        }

        $publicRoot = rtrim(public_path(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        if (str_starts_with($normalizedPath, $publicRoot)) {
            return $normalizedPath;
        }

        $audioRelativePath = self::relativePath($normalizedPath);

        if ($audioRelativePath === null) {
            return null;
        }

        $publicPath = public_path($audioRelativePath);

        if (file_exists($publicPath)) {
            return $publicPath;
        }

        return null;
    }

    public static function url(?string $path): ?string
    {
        $browserPath = self::browserPath($path);

        return $browserPath !== null ? asset($browserPath) : null;
    }

    public static function browserPath(?string $path): ?string
    {
        $normalizedPath = self::normalize($path);

        if ($normalizedPath === null) {
            return null;
        }

        if (self::isExternalUrl($normalizedPath)) {
            return $normalizedPath;
        }

        $publicRoot = rtrim(public_path(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        if (str_starts_with($normalizedPath, $publicRoot)) {
            return ltrim(Str::after($normalizedPath, $publicRoot), '/');
        }

        return self::relativePath($normalizedPath);
    }

    private static function relativePath(string $path): ?string
    {
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'audio/')) {
            return $path;
        }

        $audioRoot = rtrim(realpath(public_path('audio')) ?: public_path('audio'), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $absolutePath = '/'.$path;

        if (str_starts_with($absolutePath, $audioRoot)) {
            return 'audio/'.ltrim(Str::after($absolutePath, $audioRoot), '/');
        }

        return 'audio/'.$path;
    }

    private static function normalize(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        return str_replace('\\', '/', trim($path));
    }

    private static function isExternalUrl(string $path): bool
    {
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
    }
}
