<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'company_name', 'company_code', 'vat', 'address', 'phone', 'contact_person', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_admin' => 'boolean',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function favoriteAlbumTracks(): BelongsToMany
    {
        return $this->belongsToMany(AlbumTrack::class, 'favorite_tracks')
            ->withTimestamps();
    }

    public function playlists(): HasMany
    {
        return $this->hasMany(Playlist::class);
    }

    public function trackDownloads(): HasMany
    {
        return $this->hasMany(TrackDownload::class);
    }

    public function musicUsageEvents(): HasMany
    {
        return $this->hasMany(MusicUsageEvent::class);
    }

    public function musicUsageReports(): HasMany
    {
        return $this->hasMany(MusicUsageReport::class);
    }

    public function trackPlays(): HasMany
    {
        return $this->hasMany(TrackPlay::class);
    }
}
