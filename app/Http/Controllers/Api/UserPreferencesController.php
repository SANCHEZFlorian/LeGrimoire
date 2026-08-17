<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPreferencesController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        return response()->json([
            'rating_system' => $user->rating_system ?? '20_points',
            'reading_goal_annual' => $user->reading_goal_annual,
            'theme_preference' => $user->theme_preference ?? 'auto',
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'rating_system' => 'sometimes|in:5_stars,10_points,20_points',
            'reading_goal_annual' => 'sometimes|nullable|integer|min:1|max:999',
            'theme_preference' => 'sometimes|string|max:50',
        ]);

        $user = Auth::user();
        $user->update($validated);

        return response()->json([
            'message' => 'Préférences mises à jour.',
            'rating_system' => $user->rating_system,
            'reading_goal_annual' => $user->reading_goal_annual,
            'theme_preference' => $user->theme_preference,
        ]);
    }
}
