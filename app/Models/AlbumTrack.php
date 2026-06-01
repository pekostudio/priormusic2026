<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class AlbumTrack extends Model
{
    /** @use HasFactory<\Database\Factories\AlbumTrackFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'album_id',
        'track_number',
        'name',
        'file_name',
        'file_size',
        'bucket',
        'key',
        'download_token',
        'local_file_path',
        'downloaded_at',
        'item_type',
        'waveform_peaks',
        'waveform_version',
        'waveform_generated_at',
    ];

    /**
     * Get the album that owns the track.
     */
    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    /**
     * Get the tracks associated with this album track.
     */
    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class);
    }

    /**
     * Get the users who favorited this album track.
     */
    public function favoredByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorite_tracks')
            ->withTimestamps();
    }

    /**
     * Get the playlists that include this album track.
     */
    public function playlists(): BelongsToMany
    {
        return $this->belongsToMany(Playlist::class, 'playlist_tracks')
            ->withTimestamps();
    }

    /**
     * Get the download history entries for this album track.
     */
    public function trackDownloads(): HasMany
    {
        return $this->hasMany(TrackDownload::class);
    }

    /**
     * Get the usage reporting events for this album track.
     */
    public function musicUsageEvents(): HasMany
    {
        return $this->hasMany(MusicUsageEvent::class);
    }

    /**
     * Get the play history entries for this album track.
     */
    public function trackPlays(): HasMany
    {
        return $this->hasMany(TrackPlay::class);
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
            'file_size' => 'integer',
            'downloaded_at' => 'datetime',
            'waveform_peaks' => 'array',
            'waveform_generated_at' => 'datetime',
        ];
    }
}
