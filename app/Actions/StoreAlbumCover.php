<?php

namespace App\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class StoreAlbumCover
{
    public static function directoryForDisplayTitle(?string $displayTitle): string
    {
        return 'audio/'.self::safePathSegment($displayTitle ?: 'Unassigned');
    }

    public static function fileNameForUpload(UploadedFile $file): string
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');

        return self::safePathSegment($name).'.'.$extension;
    }

    private static function safePathSegment(string $value): string
    {
        $segment = Str::of($value)
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9]+/', '_')
            ->trim('_')
            ->toString();

        return $segment !== '' ? $segment : (string) Str::ulid();
    }
}
