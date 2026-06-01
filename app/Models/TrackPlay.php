<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class TrackPlay extends Model
{
    /** @use HasFactory<\Database\Factories\TrackPlayFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'album_track_id',
        'played_at',
        'duration_seconds',
    ];

    /**
     * Get the user who played the track.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the played album track.
     */
    public function albumTrack(): BelongsTo
    {
        return $this->belongsTo(AlbumTrack::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'played_at' => 'datetime',
            'duration_seconds' => 'integer',
        ];
    }
}
