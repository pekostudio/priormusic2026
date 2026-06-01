<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    /** @use HasFactory<\Database\Factories\TrackFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'album_id',
        'album_track_id',
        'track_number',
        'name',
        'display_title',
        'version',
        'time',
        'lenght_seconds',
        'genre',
        'tempo',
        'bpm',
        'composer',
        'publisher',
        'instrumentation',
        'cd_code',
        'comment',
        'cover',
        'release_date',
        'status',
        'keywords',
        'stem_count',
        'is_alternative',
        'api_status',
        'source_metadata',
    ];

    /**
     * Get the album that owns the track.
     */
    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    /**
     * Get the album track associated with the track.
     */
    public function albumTrack(): BelongsTo
    {
        return $this->belongsTo(AlbumTrack::class);
    }

    /**
     * Get normalized keyword tags for this track.
     */
    public function keywordTags(): BelongsToMany
    {
        return $this->belongsToMany(Keyword::class)->withTimestamps();
    }

    /**
     * Get normalized genre tags for this track.
     */
    public function genreTags(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class)->withTimestamps();
    }

    /**
     * Get alternate track records where this track is the source.
     */
    public function alternateTracks(): HasMany
    {
        return $this->hasMany(AlternateTrack::class);
    }

    /**
     * Get alternate track records where this track is the alternate source.
     */
    public function linkedAlternateTracks(): HasMany
    {
        return $this->hasMany(AlternateTrack::class, 'alternate_track_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'track_number' => 'integer',
            'lenght_seconds' => 'integer',
            'bpm' => 'integer',
            'release_date' => 'date',
            'stem_count' => 'integer',
            'is_alternative' => 'integer',
            'source_metadata' => 'array',
        ];
    }
}
