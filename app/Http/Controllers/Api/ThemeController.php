<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThemeController extends Controller
{
    public function getTheme(Request $request)
    {
        $user = auth('sanctum')->user();
        
        $mode = $user?->theme_mode ?? 'auto';
        $themeName = $user?->theme_name ?? 'grimoire';
        $fixedSeason = $user?->fixed_season;

        // Calcul de la saison actuelle
        $month = (int) date('n');
        $currentSeason = 'hiver';
        if ($month >= 3 && $month <= 5) {
            $currentSeason = 'printemps';
        } elseif ($month >= 6 && $month <= 8) {
            $currentSeason = 'ete';
        } elseif ($month >= 9 && $month <= 11) {
            $currentSeason = 'automne';
        }

        $activeSeason = $currentSeason;
        if ($mode === 'fixed') {
            $activeSeason = $fixedSeason ?? $currentSeason;
        }

        $themesConfig = config('themes');
        
        // Fallback de sécurité si le thème demandé n'existe pas dans le config
        if (!in_array($themeName, $themesConfig['available_themes'] ?? [])) {
            $themeName = 'grimoire';
        }

        // On renvoie juste le nom du thème et la saison active.
        // Les variables CSS sont maintenant gérées en frontend via les fichiers CSS.
        return response()->json([
            'theme_mode' => $mode,
            'theme_name' => $themeName,
            'active_season' => $activeSeason,
            // Fallback pour la compatibilité
            'preference' => $mode,
            'css_variables' => $themesConfig[$themeName] ?? []
        ]);
    }

    public function updateTheme(Request $request)
    {
        $validated = $request->validate([
            'theme_mode' => 'required|in:auto,fixed,chronicle_auto',
            'theme_name' => 'required|string',
            'fixed_season' => 'nullable|string'
        ]);

        $user = Auth::user();
        $user->update([
            'theme_mode' => $validated['theme_mode'],
            'theme_name' => $validated['theme_name'],
            'fixed_season' => $validated['fixed_season']
        ]);

        return $this->getTheme($request);
    }
}
