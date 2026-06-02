<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CommaSeparatedTagParser
{
    /**
     * Parse a raw separated tag string into normalized tag values.
     *
     * @return Collection<int, array{name:string, slug:string}>
     */
    public function parse(?string $tags): Collection
    {
        if ($tags === null || trim($tags) === '') {
            return collect();
        }

        return Str::of($tags)
            ->replace(['|', ';'], ',')
            ->explode(',')
            ->map(fn (string $tag): string => trim(preg_replace('/\s+/', ' ', $tag) ?? ''))
            ->filter()
            ->map(fn (string $tag): array => [
                'name' => $tag,
                'slug' => Str::slug($tag),
            ])
            ->filter(fn (array $tag): bool => $tag['slug'] !== '')
            ->unique('slug')
            ->values();
    }
}
