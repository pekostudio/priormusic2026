<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class MusicUsageEvent extends Model
{
    /** @use HasFactory<\Database\Factories\MusicUsageEventFactory> */
    use HasFactory;

    public const string TypeListened = 'listened';

    public const string TypeDownloaded = 'downloaded';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'album_track_id',
        'event_type',
        'occurred_at',
        'duration_seconds',
        'track_title',
        'album_title',
        'metadata',
    ];

    /**
     * Get the user who triggered the usage event.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the album track used by this event.
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
            'occurred_at' => 'datetime',
            'duration_seconds' => 'integer',
            'metadata' => 'array',
        ];
    }
}
