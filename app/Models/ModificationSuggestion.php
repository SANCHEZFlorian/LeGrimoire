<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModificationSuggestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'field',
        'suggested_value',
        'status',
        'upvotes_count',
        'downvotes_count',
    ];

    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
