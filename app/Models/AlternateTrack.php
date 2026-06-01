<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class AlternateTrack extends Model
{
    /** @use HasFactory<\Database\Factories\AlternateTrackFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'track_id',
        'alternate_track_id',
        'mood',
        'music_for',
        'track_number',
        'time',
        'lenght_seconds',
        'comment',
        'composer',
        'publisher',
        'artist',
        'name',
        'album_id',
        'library_id',
        'keywords',
        'lyrics',
        'display_title',
        'genre',
        'tempo',
        'instrumentation',
        'bpm',
        'frequency',
        'bitrate',
        'date_ingested',
        'version',
        'status',
        'cd_code',
        'is_alternate',
        'is_cached',
        'stem_count',
        'library_featured',
        'highlighted',
        'originator',
        'has_lyrics',
        'is_explicit',
        'release_date',
    ];

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
            'frequency' => 'integer',
            'bitrate' => 'integer',
            'date_ingested' => 'datetime',
            'is_alternate' => 'boolean',
            'is_cached' => 'boolean',
            'stem_count' => 'integer',
            'library_featured' => 'boolean',
            'highlighted' => 'boolean',
            'has_lyrics' => 'boolean',
            'is_explicit' => 'boolean',
            'release_date' => 'date',
        ];
    }

    /**
     * Get the primary track for this alternate track.
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    /**
     * Get the alternate source track.
     */
    public function alternateTrack(): BelongsTo
    {
        return $this->belongsTo(Track::class, 'alternate_track_id');
    }

    /**
     * Get the album that owns the alternate track.
     */
    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    /**
     * Get the library that owns the alternate track.
     */
    public function library(): BelongsTo
    {
        return $this->belongsTo(Library::class);
    }
}
