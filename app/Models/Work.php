<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Work extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'original_title',
        'author',
        'genres',
        'sub_genre',
        'series_name',
        'series_number',
        'audience',
        'first_publication_year',
        'created_by_user_id',
    ];

    protected $casts = [
        'genres' => 'array',
        'first_publication_year' => 'integer',
    ];

    public function editions(): HasMany
    {
        return $this->hasMany(Edition::class)->orderBy('publication_date', 'asc');
    }

    public function readings(): HasMany
    {
        return $this->hasMany(Reading::class);
    }

    public function customAwards(): HasMany
    {
        return $this->hasMany(CustomAward::class);
    }

    public function modificationSuggestions()
    {
        return $this->morphMany(ModificationSuggestion::class, 'target');
    }

    public function revisionHistories()
    {
        return $this->morphMany(RevisionHistory::class, 'auditable');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Accesseur pour récupérer une couverture par défaut (celle de la première édition).
     */
    public function getCoverUrlAttribute(): ?string
    {
        return $this->editions->firstWhere('cover_url', '!=', null)?->cover_url
            ?? $this->editions->first()?->cover_url;
    }

    /**
     * Accesseur pour récupérer une couleur de tranche par défaut.
     */
    public function getSpineColorAttribute(): string
    {
        return $this->editions->first()?->spine_color ?? '#8B4513';
    }
}
