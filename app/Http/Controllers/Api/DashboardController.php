<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $year = $request->query('year', date('Y'));

        // 1. Tous les livres en cours de lecture
        $currentReadings = $user->readings()
            ->where('status', 'reading')
            ->with(['work', 'edition'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // 2. Livres terminés (cette année)
        $finishedThisYear = $user->readings()
            ->where('status', 'finished')
            ->whereYear('end_date', $year)
            ->with(['work', 'edition'])
            ->get();

        $totalFinished = $finishedThisYear->count();

        // 3. Total pages lues (livres terminés cette année)
        $totalPagesRead = $finishedThisYear->sum(function ($r) {
            return $r->pages_count ?? $r->edition?->default_pages_count ?? 0;
        });

        // 4. Progression totale de lecture (tous les livres en cours ou lus)
        $allReadings = $user->readings()->with(['work', 'edition'])->get();
        $allCurrentPages = $allReadings->sum('current_page');
        $allTotalPages = $allReadings->sum(function ($r) {
            return $r->pages_count ?? $r->edition?->default_pages_count ?? 0;
        });
        $progressionTotale = $allTotalPages > 0 ? round(($allCurrentPages / $allTotalPages) * 100) : 0;

        // 5. Bilan annuel — livres terminés par mois
        $monthlyStats = array_fill(0, 12, 0);
        foreach ($finishedThisYear as $r) {
            if ($r->end_date) {
                $month = (int) Carbon::parse($r->end_date)->format('n');
                if ($month >= 1 && $month <= 12) {
                    $monthlyStats[$month - 1]++;
                }
            }
        }

        // 6. Genre principal (cette année, livres terminés)
        $genreCounts = [];
        foreach ($finishedThisYear as $r) {
            $genres = $r->work?->genres ?? [];
            if (is_array($genres)) {
                foreach ($genres as $g) {
                    if ($g) $genreCounts[$g] = ($genreCounts[$g] ?? 0) + 1;
                }
            } elseif (!empty($r->work?->genre)) {
                $genreCounts[$r->work->genre] = ($genreCounts[$r->work->genre] ?? 0) + 1;
            }
        }
        arsort($genreCounts);
        $favoriteGenre = !empty($genreCounts) ? array_key_first($genreCounts) : null;

        // 7. Livre le plus long (terminé cette année)
        $longestReading = $finishedThisYear->sortByDesc(function ($r) {
            return $r->pages_count ?? $r->edition?->default_pages_count ?? 0;
        })->first();

        $longestBook = $longestReading ? [
            'title' => $longestReading->work?->title ?? 'Titre inconnu',
            'pages_count' => $longestReading->pages_count ?? $longestReading->edition?->default_pages_count ?? 0,
        ] : null;

        // 8. Format principal (cette année)
        $mainFormat = $user->readings()
            ->where('status', 'finished')
            ->whereYear('end_date', $year)
            ->whereNotNull('format')
            ->select('format', DB::raw('count(*) as total'))
            ->groupBy('format')
            ->orderBy('total', 'desc')
            ->first();

        // 9. Auteur principal (cette année)
        $authorCounts = [];
        foreach ($finishedThisYear as $r) {
            $author = $r->work?->author;
            if ($author) {
                $authorCounts[$author] = ($authorCounts[$author] ?? 0) + 1;
            }
        }
        arsort($authorCounts);
        $mainAuthor = !empty($authorCounts) ? array_key_first($authorCounts) : null;

        // 10. Meilleur mois
        $bestMonth = null;
        $bestMonthCount = 0;
        foreach ($monthlyStats as $idx => $count) {
            if ($count > $bestMonthCount) {
                $bestMonthCount = $count;
                $bestMonth = $idx + 1;
            }
        }
        $bestMonthName = $bestMonth ? Carbon::create()->month($bestMonth)->locale('fr')->monthName : null;

        // 11. Heures écoutées (audiobooks terminés cette année)
        $listeningEntries = $user->readings()
            ->where('status', 'finished')
            ->whereYear('end_date', $year)
            ->whereNotNull('listening_hours')
            ->pluck('listening_hours');

        $totalListeningSeconds = 0;
        foreach ($listeningEntries as $entry) {
            $parts = explode(':', $entry);
            if (count($parts) === 3) {
                $totalListeningSeconds += ($parts[0] * 3600) + ($parts[1] * 60) + $parts[2];
            }
        }
        $totalListeningFormatted = sprintf(
            '%d:%02d:%02d',
            floor($totalListeningSeconds / 3600),
            floor(($totalListeningSeconds % 3600) / 60),
            $totalListeningSeconds % 60
        );

        // 12. Dernier livre terminé
        $lastFinished = $user->readings()
            ->where('status', 'finished')
            ->with(['work', 'edition'])
            ->orderBy('end_date', 'desc')
            ->first();

        // 13. Vues par mois
        $monthlyReads = $finishedThisYear->groupBy(function ($val) {
            return (int) Carbon::parse($val->end_date)->format('n');
        });

        $monthlyReadsFormatted = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyReadsFormatted[$i] = $monthlyReads->has($i) ? $monthlyReads[$i]->values() : [];
        }

        // 14. Objectif annuel
        $readingGoal = $user->reading_goal_annual;
        $goalProgress = $readingGoal && $readingGoal > 0
            ? min(100, round(($totalFinished / $readingGoal) * 100))
            : null;

        // 15. Années disponibles
        $availableYears = $user->readings()
            ->whereNotNull('end_date')
            ->pluck('end_date')
            ->map(fn($d) => (int) Carbon::parse($d)->format('Y'))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        if (!in_array((int) date('Y'), $availableYears)) {
            $availableYears[] = (int) date('Y');
            sort($availableYears);
        }

        return response()->json([
            'current_readings' => $currentReadings,
            'total_finished' => $totalFinished,
            'total_pages_read' => (int) $totalPagesRead,
            'progression_totale' => $progressionTotale,
            'monthly_stats' => $monthlyStats,
            'current_year' => (int) $year,
            'available_years' => $availableYears,
            'favorite_genre' => $favoriteGenre,
            'longest_book' => $longestBook,
            'main_format' => $mainFormat ? $mainFormat->format : null,
            'main_author' => $mainAuthor,
            'best_month' => $bestMonthName,
            'best_month_count' => $bestMonthCount,
            'heures_ecoutees' => $totalListeningFormatted,
            'last_finished' => $lastFinished,
            'monthly_reads' => $monthlyReadsFormatted,
            'reading_goal' => $readingGoal,
            'reading_goal_progress' => $goalProgress,
            'rating_system' => $user->rating_system ?? '20_points',
        ]);
    }
}
