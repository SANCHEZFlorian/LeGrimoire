<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reading;
use App\Models\Work;
use App\Models\Edition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReadingController extends Controller
{
    public function index()
    {
        return Auth::user()->readings()
            ->with(['work', 'edition', 'work.customAwards' => function ($query) {
                $query->where('user_id', Auth::id());
            }])
            ->get();
    }

    private function getMaxRating(): int
    {
        $system = Auth::user()->rating_system ?? '20_points';
        return match ($system) {
            '5_stars' => 5,
            '10_points' => 10,
            default => 20,
        };
    }

    public function store(Request $request)
    {
        $maxRating = $this->getMaxRating();

        $validated = $request->validate([
            // Références directes si l'œuvre et l'édition existent déjà
            'work_id' => 'nullable|exists:works,id',
            'edition_id' => 'nullable|exists:editions,id',

            // Infos Œuvre (au cas où on crée à la volée)
            'title' => 'required_without:work_id|string|max:255',
            'author' => 'required_without:work_id|string|max:255',
            'original_title' => 'nullable|string|max:255',
            'genres' => 'nullable|array',
            'series_name' => 'nullable|string|max:255',
            'series_number' => 'nullable|string|max:255',
            'audience' => 'nullable|string|max:255',

            // Infos Édition (au cas où on crée à la volée)
            'publisher' => 'nullable|string|max:255',
            'publication_date' => 'nullable|date',
            'isbn' => 'nullable|string|max:255',
            'cover_url' => 'nullable|url',
            'cover_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'summary' => 'nullable|string',
            'language' => 'nullable|string|max:20',
            'default_pages_count' => 'nullable|integer',

            // Infos propres au Lecteur (Mon exemplaire / Ma lecture)
            'format' => 'nullable|string|max:100',
            'pages_count' => 'nullable|integer|min:1', // Pages réelles du lecteur
            'status' => 'nullable|in:to_read,reading,finished,past_read,wishlist,dnf',
            'current_page' => 'nullable|integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'rating' => "nullable|numeric|min:0|max:{$maxRating}",
            'spice_rating' => 'nullable|integer|min:0|max:5',
            'review' => 'nullable|string',
            'quotes' => 'nullable|array',
            'tropes' => 'nullable|array',
            'reading_time_minutes' => 'nullable|integer|min:0',
            'listening_hours' => 'nullable|string|max:20',
            'audio_duration' => 'nullable|string|max:20',
            'priority' => 'nullable|string|max:255',
            'is_favorite' => 'nullable|boolean',
            'favorite_quote' => 'nullable|string',
            'favorite_moments' => 'nullable|array',
            'favorite_moments.*.text' => 'nullable|string',
            'favorite_moments.*.attribution' => 'nullable|string|max:255',
            'favorite_moment' => 'nullable|string',
            'is_bought' => 'nullable|boolean',
            'is_reread' => 'nullable|boolean',
            'is_dnf' => 'nullable|boolean',
            'story_type' => 'nullable|string|max:255',
        ]);

        // 1. Déterminer / Trouver l'Œuvre
        $work = null;
        if (!empty($validated['work_id'])) {
            $work = Work::find($validated['work_id']);
        }

        if (!$work) {
            $genres = array_values(array_unique(array_filter($validated['genres'] ?? [])));
            $work = Work::firstOrCreate(
                [
                    'title' => $validated['title'],
                    'author' => $validated['author']
                ],
                [
                    'original_title' => $validated['original_title'] ?? null,
                    'genres' => $genres,
                    'series_name' => $validated['series_name'] ?? null,
                    'series_number' => $validated['series_number'] ?? null,
                    'audience' => $validated['audience'] ?? null,
                    'created_by_user_id' => Auth::id(),
                ]
            );
        }

        // 2. Déterminer / Trouver l'Édition
        $edition = null;
        if (!empty($validated['edition_id'])) {
            $edition = Edition::find($validated['edition_id']);
        }

        if (!$edition) {
            // Traitement de la couverture
            $finalCoverUrl = $validated['cover_url'] ?? null;
            if ($request->hasFile('cover_file')) {
                $path = $request->file('cover_file')->store('covers', 'public');
                $finalCoverUrl = Storage::url($path);
            }

            $spineColor = CatalogController::extractSpineColor($finalCoverUrl);

            // Chercher une édition similaire dans cette œuvre si même ISBN ou même format/éditeur
            $editionQuery = $work->editions();
            if (!empty($validated['isbn'])) {
                $edition = $editionQuery->where('isbn', $validated['isbn'])->first();
            }

            if (!$edition) {
                $edition = $work->editions()->create([
                    'format' => $validated['format'] ?? 'Broché',
                    'publisher' => $validated['publisher'] ?? null,
                    'publication_date' => $validated['publication_date'] ?? null,
                    'isbn' => $validated['isbn'] ?? null,
                    'cover_url' => $finalCoverUrl,
                    'spine_color' => $spineColor,
                    'summary' => $validated['summary'] ?? null,
                    'language' => $validated['language'] ?? 'fr',
                    'default_pages_count' => $validated['default_pages_count'] ?? $validated['pages_count'] ?? null,
                    'created_by_user_id' => Auth::id(),
                ]);
            }
        }

        // 3. Vérifier si l'utilisateur possède déjà cette édition / œuvre
        $existingReading = Auth::user()->readings()
            ->where(function ($q) use ($work, $edition) {
                $q->where('edition_id', $edition->id)
                  ->orWhere(function ($q2) use ($work) {
                      $q2->where('work_id', $work->id)->whereNull('edition_id');
                  });
            })
            ->first();

        if ($existingReading) {
            return response()->json([
                'message' => 'Ce livre ou cette édition est déjà dans votre bibliothèque.',
                'reading' => $existingReading->load(['work', 'edition'])
            ], 409);
        }

        // 4. Nombre de pages personnalisé du lecteur
        $readerPages = $validated['pages_count'] ?? $edition->default_pages_count ?? null;

        // 5. Créer la lecture (Mon exemplaire)
        $readingData = [
            'work_id' => $work->id,
            'edition_id' => $edition->id,
            'format' => $validated['format'] ?? $edition->format ?? 'Broché',
            'pages_count' => $readerPages,
            'audio_duration' => $validated['audio_duration'] ?? $validated['listening_hours'] ?? null,
            'status' => $validated['status'] ?? 'to_read',
            'current_page' => $validated['current_page'] ?? 0,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'rating' => $validated['rating'] ?? null,
            'spice_rating' => $validated['spice_rating'] ?? null,
            'review' => $validated['review'] ?? null,
            'quotes' => $validated['quotes'] ?? null,
            'tropes' => $validated['tropes'] ?? null,
            'language' => $validated['language'] ?? $edition->language ?? 'fr',
            'reading_time_minutes' => $validated['reading_time_minutes'] ?? null,
            'listening_hours' => $validated['listening_hours'] ?? $validated['audio_duration'] ?? null,
            'priority' => $validated['priority'] ?? null,
            'is_favorite' => $validated['is_favorite'] ?? false,
            'favorite_quote' => $validated['favorite_quote'] ?? null,
            'favorite_moment' => $validated['favorite_moment'] ?? null,
            'favorite_moments' => $validated['favorite_moments'] ?? null,
            'is_bought' => $validated['is_bought'] ?? false,
            'is_reread' => $validated['is_reread'] ?? false,
            'is_dnf' => $validated['is_dnf'] ?? false,
            'story_type' => $validated['story_type'] ?? null,
        ];

        $reading = Auth::user()->readings()->create($readingData);

        return response()->json($reading->load(['work', 'edition']), 201);
    }

    public function show(Reading $reading)
    {
        if ($reading->user_id !== Auth::id()) {
            abort(403);
        }
        return $reading->load(['work', 'edition']);
    }

    public function update(Request $request, Reading $reading)
    {
        if ($reading->user_id !== Auth::id()) {
            abort(403);
        }

        $maxRating = $this->getMaxRating();

        $validated = $request->validate([
            'edition_id' => 'nullable|exists:editions,id',
            'status' => 'sometimes|required|in:to_read,reading,finished,past_read,wishlist,dnf',
            'current_page' => 'nullable|integer|min:0',
            'pages_count' => 'nullable|integer|min:1',
            'audio_duration' => 'nullable|string|max:20',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'rating' => "nullable|numeric|min:0|max:{$maxRating}",
            'spice_rating' => 'nullable|integer|min:0|max:5',
            'review' => 'nullable|string',
            'quotes' => 'nullable|array',
            'tropes' => 'nullable|array',
            'format' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:255',
            'reading_time_minutes' => 'nullable|integer|min:0',
            'listening_hours' => 'nullable|string|max:20',
            'priority' => 'nullable|string|max:255',
            'is_favorite' => 'nullable|boolean',
            'favorite_quote' => 'nullable|string',
            'favorite_moments' => 'nullable|array',
            'favorite_moments.*.text' => 'nullable|string',
            'favorite_moments.*.attribution' => 'nullable|string|max:255',
            'favorite_moment' => 'nullable|string',
            'is_bought' => 'nullable|boolean',
            'is_reread' => 'nullable|boolean',
            'is_dnf' => 'nullable|boolean',
            'story_type' => 'nullable|string|max:255',
        ]);

        $reading->update($validated);
        return response()->json($reading->load(['work', 'edition']));
    }

    public function destroy(Reading $reading)
    {
        if ($reading->user_id !== Auth::id()) {
            abort(403);
        }

        $reading->delete();
        return response()->json(null, 204);
    }
}
