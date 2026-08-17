<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('books')) {
            return;
        }

        $books = DB::table('books')->get();

        foreach ($books as $book) {
            // Traitement des genres multiples
            $genres = [];
            if (!empty($book->genre)) {
                $parts = preg_split('/[,;\/|]+/', $book->genre);
                foreach ($parts as $p) {
                    $trimmed = trim($p);
                    if ($trimmed !== '' && !in_array($trimmed, $genres)) {
                        $genres[] = $trimmed;
                    }
                }
            }
            if (!empty($book->sub_genre)) {
                $subParts = preg_split('/[,;\/|]+/', $book->sub_genre);
                foreach ($subParts as $sp) {
                    $trimmed = trim($sp);
                    if ($trimmed !== '' && !in_array($trimmed, $genres)) {
                        $genres[] = $trimmed;
                    }
                }
            }

            // Année de 1ère publication
            $pubYear = null;
            if (!empty($book->publication_date)) {
                $pubYear = (int) substr($book->publication_date, 0, 4);
            }

            // Chercher ou créer l'œuvre
            $existingWork = DB::table('works')
                ->where('title', $book->title)
                ->where('author', $book->author)
                ->first();

            if ($existingWork) {
                $workId = $existingWork->id;
            } else {
                $workId = DB::table('works')->insertGetId([
                    'title' => $book->title,
                    'original_title' => null,
                    'author' => $book->author,
                    'genres' => json_encode($genres, JSON_UNESCAPED_UNICODE),
                    'sub_genre' => $book->sub_genre,
                    'series_name' => $book->series_name,
                    'series_number' => $book->series_number,
                    'audience' => $book->audience,
                    'first_publication_year' => $pubYear,
                    'created_at' => $book->created_at ?? now(),
                    'updated_at' => $book->updated_at ?? now(),
                ]);
            }

            // Créer une édition pour ce livre
            $editionId = DB::table('editions')->insertGetId([
                'work_id' => $workId,
                'format' => 'Broché',
                'publisher' => $book->publisher,
                'publication_date' => $book->publication_date,
                'isbn' => $book->isbn,
                'cover_url' => $book->cover_url,
                'spine_color' => $book->spine_color ?? '#8B4513',
                'summary' => $book->summary,
                'language' => 'fr',
                'default_pages_count' => $book->pages_count,
                'default_audio_duration' => null,
                'created_at' => $book->created_at ?? now(),
                'updated_at' => $book->updated_at ?? now(),
            ]);

            // Mettre à jour les readings liés
            DB::table('readings')
                ->where('book_id', $book->id)
                ->update([
                    'work_id' => $workId,
                    'edition_id' => $editionId,
                    'pages_count' => $book->pages_count,
                ]);

            // Mettre à jour les custom awards
            if (Schema::hasTable('custom_awards')) {
                DB::table('custom_awards')
                    ->where('book_id', $book->id)
                    ->update([
                        'work_id' => $workId,
                        'edition_id' => $editionId,
                    ]);
            }
        }
    }

    public function down(): void
    {
        // No down migration needed for data copy
    }
};
