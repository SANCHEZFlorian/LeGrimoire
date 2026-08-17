<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reading extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'work_id',
        'edition_id',
        'status',
        'current_page',
        'pages_count',
        'audio_duration',
        'start_date',
        'end_date',
        'rating',
        'spice_rating',
        'review',
        'quotes',
        'tropes',
        'format',
        'language',
        'reading_time_minutes',
        'listening_hours',
        'priority',
        'is_favorite',
        'favorite_quote',
        'favorite_moments',
        'favorite_moment',
        'is_bought',
        'is_reread',
        'is_dnf',
        'story_type',
    ];

    protected $casts = [
        'current_page' => 'integer',
        'pages_count' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_favorite' => 'boolean',
        'is_bought' => 'boolean',
        'favorite_moments' => 'array',
        'quotes' => 'array',
        'tropes' => 'array',
        'spice_rating' => 'integer',
        'reading_time_minutes' => 'integer',
        'is_reread' => 'boolean',
        'is_dnf' => 'boolean',
    ];

    protected $appends = ['book'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function legacyBook(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    /**
     * Accesseur rétro-compatible $reading->book qui fusionne Work et Edition
     */
    public function getBookAttribute()
    {
        $work = $this->work;
        $edition = $this->edition;

        if (!$work && !$edition && $this->book_id) {
            return $this->legacyBook;
        }

        // Genres formatting (array to string if needed or array)
        $genres = $work?->genres ?? [];
        $genreString = is_array($genres) ? implode(', ', $genres) : ($genres ?: null);

        return (object) [
            'id' => $edition?->id ?? $work?->id ?? $this->book_id,
            'work_id' => $work?->id,
            'edition_id' => $edition?->id,
            'title' => $work?->title ?? 'Titre inconnu',
            'original_title' => $work?->original_title,
            'author' => $work?->author ?? 'Auteur inconnu',
            'genres' => $genres,
            'genre' => $genreString,
            'sub_genre' => $work?->sub_genre,
            'series_name' => $work?->series_name,
            'series_number' => $work?->series_number,
            'audience' => $work?->audience,
            'publisher' => $edition?->publisher,
            'publication_date' => $edition?->publication_date?->format('Y-m-d'),
            'isbn' => $edition?->isbn,
            'cover_url' => $edition?->cover_url ?? $work?->cover_url,
            'spine_color' => $edition?->spine_color ?? $work?->spine_color ?? '#8B4513',
            'summary' => $edition?->summary,
            'format' => $edition?->format ?? $this->format,
            'language' => $edition?->language ?? $this->language ?? 'fr',
            'pages_count' => $this->pages_count ?? null,
            'audio_duration' => $this->audio_duration ?? null,
            'custom_awards' => $work ? $work->customAwards()->where('user_id', $this->user_id)->get() : collect(),
        ];
    }
}
