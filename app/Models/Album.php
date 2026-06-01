<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    /** @use HasFactory<\Database\Factories\AlbumFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'library_id',
        'displaytitle',
        'featured',
        'releasedate',
        'code',
        'detail',
        'cover',
        'name',
        'status',
        'libraryfeatured',
        'source_metadata',
    ];

    /**
     * Get the library that owns the album.
     */
    public function library(): BelongsTo
    {
        return $this->belongsTo(Library::class);
    }

    /**
     * Get the tracks for the album.
     */
    public function albumTracks(): HasMany
    {
        return $this->hasMany(AlbumTrack::class);
    }

    /**
     * Get the tracks for the album.
     */
    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class);
    }

    /**
     * Get the alternate tracks for the album.
     */
    public function alternateTracks(): HasMany
    {
        return $this->hasMany(AlternateTrack::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'releasedate' => 'date',
            'status' => 'boolean',
            'source_metadata' => 'array',
        ];
    }
}
