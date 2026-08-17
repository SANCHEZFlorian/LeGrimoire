<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'feature_suggestion_id'])]
class FeatureSuggestionVote extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function featureSuggestion(): BelongsTo
    {
        return $this->belongsTo(FeatureSuggestion::class);
    }
    //
}
