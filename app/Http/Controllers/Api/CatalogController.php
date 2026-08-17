<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Work;
use App\Models\Edition;
use App\Models\Reading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\GoogleBooksService;
use League\ColorExtractor\Color;
use League\ColorExtractor\ColorExtractor;
use League\ColorExtractor\Palette;

class CatalogController extends Controller
{
    /**
     * Extraire la couleur dominante de tranche depuis l'URL d'une couverture
     */
    public static function extractSpineColor(?string $coverUrl): string
    {
        $defaultColor = '#8B4513';
        if (!$coverUrl) return $defaultColor;

        try {
            $context = stream_context_create([
                'http' => [
                    'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n",
                    'timeout' => 5
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ]
            ]);

            $imageContent = @file_get_contents($coverUrl, false, $context);
            if (!$imageContent) return $defaultColor;

            $image = @imagecreatefromstring($imageContent);
            if (!$image) return $defaultColor;

            $palette = Palette::fromGD($image);
            $extractor = new ColorExtractor($palette);
            $colors = $extractor->extract(1);
            imagedestroy($image);

            if (!empty($colors)) {
                return Color::fromIntToHex($colors[0]);
            }
        } catch (\Exception $e) {
            // Ignorer et retourner la couleur par défaut
        }

        return $defaultColor;
    }

    /**
     * Liste des œuvres du catalogue ("THE bibliothèque") avec filtres
     */
    public function index(Request $request, GoogleBooksService $googleBooksService)
    {
        $query = Work::with(['editions' => function ($q) {
            $q->orderBy('publication_date', 'asc');
        }]);

        // Recherche textuelle (titre, auteur, saga)
        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('series_name', 'like', "%{$search}%")
                  ->orWhereHas('editions', function ($eq) use ($search) {
                      $eq->where('isbn', 'like', "%{$search}%")
                         ->orWhere('publisher', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par genre
        if ($genre = $request->query('genre')) {
            if ($genre !== 'all') {
                $query->where(function ($q) use ($genre) {
                    $q->whereJsonContains('genres', $genre)
                      ->orWhere('sub_genre', 'like', "%{$genre}%");
                });
            }
        }

        // Filtre par auteur
        if ($author = $request->query('author')) {
            $query->where('author', $author);
        }

        // Filtre par saga
        if ($series = $request->query('series')) {
            $query->where('series_name', $series);
        }

        $works = $query->orderBy('title', 'asc')->get();

        // Récupérer pour l'utilisateur connecté les IDs d'éditions/œuvres qu'il possède
        $userReadingWorkIds = [];
        $userReadingEditionIds = [];
        if (Auth::check()) {
            $userReadings = Auth::user()->readings()->get(['work_id', 'edition_id', 'status']);
            $userReadingWorkIds = $userReadings->pluck('work_id')->filter()->unique()->toArray();
            $userReadingEditionIds = $userReadings->pluck('edition_id')->filter()->unique()->toArray();
        }

        $works->transform(function ($work) use ($userReadingWorkIds, $userReadingEditionIds) {
            $work->in_user_library = in_array($work->id, $userReadingWorkIds);
            $work->editions->transform(function ($edition) use ($userReadingEditionIds) {
                $edition->in_user_library = in_array($edition->id, $userReadingEditionIds);
                return $edition;
            });
            return $work;
        });

        $googleResults = [];
        if ($search = $request->query('q')) {
            $lang = $request->query('lang', 'fr');
            $googleResults = $googleBooksService->searchBooks($search, $lang);
        }

        return response()->json([
            'catalog_results' => $works,
            'google_results' => $googleResults,
        ]);
    }

    /**
     * Détail d'une œuvre avec toutes ses éditions
     */
    public function show(Work $work)
    {
        $work->load(['editions' => function ($q) {
            $q->orderBy('publication_date', 'asc');
        }]);

        if (Auth::check()) {
            $userReadings = Auth::user()->readings()->where('work_id', $work->id)->get();
            $work->user_readings = $userReadings;
        }

        return response()->json($work);
    }

    /**
     * Créer une nouvelle œuvre + 1ère édition dans "THE bibliothèque"
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Infos Œuvre
            'title' => 'required|string|max:255',
            'original_title' => 'nullable|string|max:255',
            'author' => 'required|string|max:255',
            'genres' => 'nullable|array',
            'genres.*' => 'string|max:100',
            'sub_genre' => 'nullable|string|max:255',
            'series_name' => 'nullable|string|max:255',
            'series_number' => 'nullable|string|max:255',
            'audience' => 'nullable|string|max:255',
            'first_publication_year' => 'nullable|integer',

            // Infos Édition
            'edition.format' => 'nullable|string|max:100',
            'edition.publisher' => 'nullable|string|max:255',
            'edition.publication_date' => 'nullable|date',
            'edition.isbn' => 'nullable|string|max:255',
            'edition.cover_url' => 'nullable|url',
            'edition.cover_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'edition.summary' => 'nullable|string',
            'edition.language' => 'nullable|string|max:20',
            'edition.default_pages_count' => 'nullable|integer',
            'edition.default_audio_duration' => 'nullable|string|max:30',
        ]);

        // Nettoyer les genres
        $genres = array_values(array_unique(array_filter($validated['genres'] ?? [])));

        // Chercher si l'œuvre existe déjà
        $work = Work::firstOrCreate(
            ['title' => $validated['title'], 'author' => $validated['author']],
            [
                'original_title' => $validated['original_title'] ?? null,
                'genres' => $genres,
                'sub_genre' => $validated['sub_genre'] ?? null,
                'series_name' => $validated['series_name'] ?? null,
                'series_number' => $validated['series_number'] ?? null,
                'audience' => $validated['audience'] ?? null,
                'first_publication_year' => $validated['first_publication_year'] ?? null,
                'created_by_user_id' => Auth::id(),
            ]
        );

        // Gérer la couverture
        $editionData = $request->input('edition', []);
        $finalCoverUrl = $editionData['cover_url'] ?? null;
        if ($request->hasFile('edition.cover_file')) {
            $path = $request->file('edition.cover_file')->store('covers', 'public');
            $finalCoverUrl = Storage::url($path);
        }

        $spineColor = self::extractSpineColor($finalCoverUrl);

        // Créer l'édition
        $edition = $work->editions()->create([
            'format' => $editionData['format'] ?? 'Broché',
            'publisher' => $editionData['publisher'] ?? null,
            'publication_date' => $editionData['publication_date'] ?? null,
            'isbn' => $editionData['isbn'] ?? null,
            'cover_url' => $finalCoverUrl,
            'spine_color' => $spineColor,
            'summary' => $editionData['summary'] ?? null,
            'language' => $editionData['language'] ?? 'fr',
            'default_pages_count' => $editionData['default_pages_count'] ?? null,
            'default_audio_duration' => $editionData['default_audio_duration'] ?? null,
            'created_by_user_id' => Auth::id(),
        ]);

        return response()->json($work->load('editions'), 201);
    }

    /**
     * Ajouter une nouvelle édition à une œuvre existante
     */
    public function addEdition(Request $request, Work $work)
    {
        $validated = $request->validate([
            'format' => 'nullable|string|max:100',
            'publisher' => 'nullable|string|max:255',
            'publication_date' => 'nullable|date',
            'isbn' => 'nullable|string|max:255',
            'cover_url' => 'nullable|url',
            'cover_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'summary' => 'nullable|string',
            'language' => 'nullable|string|max:20',
            'default_pages_count' => 'nullable|integer',
            'default_audio_duration' => 'nullable|string|max:30',
        ]);

        $finalCoverUrl = $validated['cover_url'] ?? null;
        if ($request->hasFile('cover_file')) {
            $path = $request->file('cover_file')->store('covers', 'public');
            $finalCoverUrl = Storage::url($path);
        }

        $spineColor = self::extractSpineColor($finalCoverUrl);

        $edition = $work->editions()->create([
            'format' => $validated['format'] ?? 'Broché',
            'publisher' => $validated['publisher'] ?? null,
            'publication_date' => $validated['publication_date'] ?? null,
            'isbn' => $validated['isbn'] ?? null,
            'cover_url' => $finalCoverUrl,
            'spine_color' => $spineColor,
            'summary' => $validated['summary'] ?? null,
            'language' => $validated['language'] ?? 'fr',
            'default_pages_count' => $validated['default_pages_count'] ?? null,
            'default_audio_duration' => $validated['default_audio_duration'] ?? null,
            'created_by_user_id' => Auth::id(),
        ]);

        return response()->json($edition, 201);
    }

    /**
     * Mettre à jour une œuvre
     */
    public function updateWork(Request $request, Work $work)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'original_title' => 'nullable|string|max:255',
            'author' => 'sometimes|required|string|max:255',
            'genres' => 'nullable|array',
            'sub_genre' => 'nullable|string|max:255',
            'series_name' => 'nullable|string|max:255',
            'series_number' => 'nullable|string|max:255',
            'audience' => 'nullable|string|max:255',
            'first_publication_year' => 'nullable|integer',
        ]);

        if (isset($validated['genres'])) {
            $validated['genres'] = array_values(array_unique(array_filter($validated['genres'])));
        }

        $work->update($validated);
        return response()->json($work->load('editions'));
    }

    /**
     * Mettre à jour une édition
     */
    public function updateEdition(Request $request, Edition $edition)
    {
        $validated = $request->validate([
            'format' => 'nullable|string|max:100',
            'publisher' => 'nullable|string|max:255',
            'publication_date' => 'nullable|date',
            'isbn' => 'nullable|string|max:255',
            'cover_url' => 'nullable|url',
            'cover_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'summary' => 'nullable|string',
            'language' => 'nullable|string|max:20',
            'default_pages_count' => 'nullable|integer',
            'default_audio_duration' => 'nullable|string|max:30',
        ]);

        if ($request->hasFile('cover_file')) {
            $path = $request->file('cover_file')->store('covers', 'public');
            $validated['cover_url'] = Storage::url($path);
            $validated['spine_color'] = self::extractSpineColor($validated['cover_url']);
        } elseif (isset($validated['cover_url']) && $validated['cover_url'] !== $edition->cover_url) {
            $validated['spine_color'] = self::extractSpineColor($validated['cover_url']);
        }

        $edition->update($validated);
        return response()->json($edition);
    }

    /**
     * Supprimer une œuvre et toutes ses éditions associées
     */
    public function destroyWork(Work $work)
    {
        $work->delete();
        return response()->json(null, 204);
    }

    /**
     * Supprimer une édition spécifique
     */
    public function destroyEdition(Edition $edition)
    {
        $edition->delete();
        return response()->json(null, 204);
    }

    /**
     * Obtenir tous les genres distincts du catalogue
     */
    public function genres()
    {
        $allWorks = Work::select('genres', 'sub_genre')->get();
        $genres = [];

        foreach ($allWorks as $w) {
            if (is_array($w->genres)) {
                foreach ($w->genres as $g) {
                    if ($g && !in_array($g, $genres)) {
                        $genres[] = $g;
                    }
                }
            }
            if ($w->sub_genre && !in_array($w->sub_genre, $genres)) {
                $genres[] = $w->sub_genre;
            }
        }

        sort($genres);
        return response()->json($genres);
    }
}
