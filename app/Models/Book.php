<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'google_books_id',
        'title',
        'author',
        'isbn',
        'publisher',
        'pages_count',
        'publication_date',
        'cover_url',
        'spine_color',
        'series_name',
        'series_number',
        'genre',
        'sub_genre',
        'summary',
        'audience',
    ];

    public function readings(): HasMany
    {
        return $this->hasMany(Reading::class);
    }

    public function customAwards(): HasMany
    {
        return $this->hasMany(CustomAward::class);
    }
}
