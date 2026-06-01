<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class TrackDownload extends Model
{
    /** @use HasFactory<\Database\Factories\TrackDownloadFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'album_track_id',
        'downloaded_at',
    ];

    /**
     * Get the user who downloaded the track.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the downloaded album track.
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
            'downloaded_at' => 'datetime',
        ];
    }
}
