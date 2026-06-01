<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class MusicUsageReport extends Model
{
    /** @use HasFactory<\Database\Factories\MusicUsageReportFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'starts_at',
        'ends_at',
        'listened_count',
        'downloaded_count',
        'duration_seconds',
        'file_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'listened_count' => 'integer',
            'downloaded_count' => 'integer',
            'duration_seconds' => 'integer',
        ];
    }
}
