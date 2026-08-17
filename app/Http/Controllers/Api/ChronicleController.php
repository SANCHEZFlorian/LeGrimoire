<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ChronicleController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $year = (int) $request->query('year', date('Y'));

        // --- Années disponibles ---
        $availableYears = $user->readings()
            ->where('status', 'finished')
            ->whereNotNull('end_date')
            ->selectRaw('DISTINCT YEAR(end_date) as year')
            ->pluck('year')
            ->sort()
            ->values()
            ->toArray();

        if (!in_array((int) date('Y'), $availableYears)) {
            $availableYears[] = (int) date('Y');
            sort($availableYears);
        }

        // --- Lectures terminées cette année (avec œuvre et édition) ---
        $readings = $user->readings()
            ->where('status', 'finished')
            ->whereYear('end_date', $year)
            ->with(['work', 'edition'])
            ->orderBy('end_date', 'desc')
            ->get();

        // --- Stats globales de l'année ---
        $totalBooks = $readings->count();

        $totalPages = $readings->sum(function ($r) {
            return $r->pages_count ?? $r->edition?->default_pages_count ?? 0;
        });

        // Heures d'écoute (format HH:MM:SS)
        $totalListeningSeconds = 0;
        foreach ($readings->whereNotNull('listening_hours') as $r) {
            $parts = explode(':', $r->listening_hours);
            if (count($parts) === 3) {
                $totalListeningSeconds += ($parts[0] * 3600) + ($parts[1] * 60) + $parts[2];
            }
        }
        $totalListening = sprintf(
            '%d:%02d:%02d',
            floor($totalListeningSeconds / 3600),
            floor(($totalListeningSeconds % 3600) / 60),
            $totalListeningSeconds % 60
        );

        // Genre principal de l'année
        $genreCounts = [];
        foreach ($readings as $r) {
            $genres = $r->work?->genres ?? [];
            if (is_array($genres)) {
                foreach ($genres as $g) {
                    if ($g) $genreCounts[$g] = ($genreCounts[$g] ?? 0) + 1;
                }
            }
        }
        arsort($genreCounts);
        $topGenre = !empty($genreCounts) ? array_key_first($genreCounts) : null;

        // Auteur principal de l'année
        $authorCounts = [];
        foreach ($readings as $r) {
            $author = $r->work?->author;
            if ($author) $authorCounts[$author] = ($authorCounts[$author] ?? 0) + 1;
        }
        arsort($authorCounts);
        $topAuthor = !empty($authorCounts) ? array_key_first($authorCounts) : null;

        // Livre favori de l'année : is_favorite d'abord, puis meilleure note
        $yearFavorite = $readings->firstWhere('is_favorite', true)
            ?? $readings->filter(fn($r) => $r->rating !== null)->sortByDesc('rating')->first();

        // Note moyenne de l'année
        $ratedReadings = $readings->filter(fn($r) => $r->rating !== null);
        $avgRating = $ratedReadings->count() > 0
            ? round($ratedReadings->avg('rating'), 1)
            : null;

        // --- Données par mois ---
        $byMonth = $readings->groupBy(function ($r) {
            return (int) Carbon::parse($r->end_date)->format('n');
        });

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthReadings = $byMonth->get($m, collect());

            if ($monthReadings->isEmpty()) {
                $months[$m] = [
                    'month_number' => $m,
                    'month_name'   => Carbon::create()->month($m)->locale('fr')->monthName,
                    'books'        => [],
                    'total_books'  => 0,
                    'total_pages'  => 0,
                    'top_genre'    => null,
                    'avg_rating'   => null,
                    'listening'    => null,
                    'favorite'     => null,
                ];
                continue;
            }

            $monthFavorite = $monthReadings->firstWhere('is_favorite', true)
                ?? $monthReadings->filter(fn($r) => $r->rating !== null)->sortByDesc('rating')->first();

            $monthGenreCounts = [];
            foreach ($monthReadings as $r) {
                $genres = $r->work?->genres ?? [];
                if (is_array($genres)) {
                    foreach ($genres as $g) {
                        if ($g) $monthGenreCounts[$g] = ($monthGenreCounts[$g] ?? 0) + 1;
                    }
                }
            }
            arsort($monthGenreCounts);
            $monthTopGenre = !empty($monthGenreCounts) ? array_key_first($monthGenreCounts) : null;

            $monthPages = $monthReadings->sum(fn($r) => $r->pages_count ?? $r->edition?->default_pages_count ?? 0);

            $monthRated = $monthReadings->filter(fn($r) => $r->rating !== null);
            $monthAvg = $monthRated->count() > 0
                ? round($monthRated->avg('rating'), 1)
                : null;

            $monthSeconds = 0;
            foreach ($monthReadings->whereNotNull('listening_hours') as $r) {
                $parts = explode(':', $r->listening_hours);
                if (count($parts) === 3) {
                    $monthSeconds += ($parts[0] * 3600) + ($parts[1] * 60) + $parts[2];
                }
            }
            $monthListening = $monthSeconds > 0
                ? sprintf('%d:%02d:%02d', floor($monthSeconds / 3600), floor(($monthSeconds % 3600) / 60), $monthSeconds % 60)
                : null;

            $months[$m] = [
                'month_number' => $m,
                'month_name'   => Carbon::create()->month($m)->locale('fr')->monthName,
                'books'        => $monthReadings->values(),
                'total_books'  => $monthReadings->count(),
                'total_pages'  => $monthPages,
                'top_genre'    => $monthTopGenre,
                'avg_rating'   => $monthAvg,
                'listening'    => $monthListening,
                'favorite'     => $monthFavorite,
            ];
        }

        return response()->json([
            'year'            => $year,
            'available_years' => $availableYears,
            'total_books'     => $totalBooks,
            'total_pages'     => (int) $totalPages,
            'total_listening' => $totalListening,
            'top_genre'       => $topGenre,
            'top_author'      => $topAuthor,
            'avg_rating'      => $avgRating,
            'year_favorite'   => $yearFavorite,
            'months'          => array_values($months),
            'rating_system'   => $user->rating_system ?? '20_points',
        ]);
    }
}
