<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Library extends Model
{
    /** @use HasFactory<\Database\Factories\LibraryFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'featured',
        'detail',
        'name',
        'library_id',
        'location',
        'website',
        'library_logo_url',
        'status',
        'last_updated',
        'codes',
        'type',
        'source_metadata',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'status' => 'boolean',
            'last_updated' => 'datetime',
            'codes' => 'array',
            'source_metadata' => 'array',
        ];
    }

    /**
     * Get the albums for the library.
     */
    public function albums(): HasMany
    {
        return $this->hasMany(Album::class);
    }

    /**
     * Get the alternate tracks for the library.
     */
    public function alternateTracks(): HasMany
    {
        return $this->hasMany(AlternateTrack::class);
    }
}
