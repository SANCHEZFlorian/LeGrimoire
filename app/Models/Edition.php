<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Edition extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_id',
        'format',
        'publisher',
        'publication_date',
        'isbn',
        'cover_url',
        'spine_color',
        'summary',
        'language',
        'created_by_user_id',
    ];

    protected $casts = [
        'publication_date' => 'date',
    ];

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function readings(): HasMany
    {
        return $this->hasMany(Reading::class);
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
}
