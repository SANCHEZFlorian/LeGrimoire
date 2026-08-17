<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModificationSuggestionVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'modification_suggestion_id',
        'user_id',
        'is_upvote',
    ];

    protected $casts = [
        'is_upvote' => 'boolean',
    ];

    public function suggestion(): BelongsTo
    {
        return $this->belongsTo(ModificationSuggestion::class, 'modification_suggestion_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
