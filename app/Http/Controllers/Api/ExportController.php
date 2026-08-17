<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Str;

class ExportController extends Controller
{
    public function exportMonthPdf(Request $request, $year, $month)
    {
        $user = Auth::user();
        
        $readings = $user->readings()
            ->with(['book', 'book.customAwards' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }])
            ->whereYear('end_date', $year)
            ->whereMonth('end_date', $month)
            ->whereIn('status', ['finished', 'past_read'])
            ->get();

        $theme = $user->theme_preference ?? 'auto';
        $themesConfig = config('themes');

        if ($theme === 'auto') {
            $m = (int) $month;
            if ($m >= 3 && $m <= 5) $theme = 'printemps';
            elseif ($m >= 6 && $m <= 8) $theme = 'ete';
            elseif ($m >= 9 && $m <= 11) $theme = 'automne';
            else $theme = 'hiver';
        }
        
        if (!isset($themesConfig[$theme])) {
            $theme = 'automne';
        }

        $cssVariables = $themesConfig[$theme];

        // Rendu de la vue Blade en HTML brut
        $html = view('pdf.month_summary', compact('readings', 'year', 'month', 'cssVariables', 'user'))->render();

        try {
            // Configuration de Browsershot
            // ->showBackground() force l'impression du background CSS et du grain de papier
            $pdfContent = Browsershot::html($html)
                ->showBackground()
                ->format('A4')
                ->margins(10, 10, 10, 10)
                ->pdf();
                
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="carnet_'.$year.'_'.$month.'.pdf"'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la génération du PDF avec Browsershot: ' . $e->getMessage()], 500);
        }
    }
}
