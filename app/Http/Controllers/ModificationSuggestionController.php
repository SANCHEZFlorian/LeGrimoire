<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\ModificationSuggestion;
use App\Models\ModificationSuggestionVote;
use App\Models\Work;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModificationSuggestionController extends Controller
{
    /**
     * Soumettre une nouvelle suggestion de modification.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'target_type' => 'required|in:work,edition',
            'target_id' => 'required|integer',
            'field' => 'required|string',
            'suggested_value' => 'required|string',
        ]);

        $targetClass = $validated['target_type'] === 'work' ? Work::class : Edition::class;
        $target = $targetClass::findOrFail($validated['target_id']);

        // Vérifier si une suggestion en attente existe déjà pour ce champ
        $existing = $target->modificationSuggestions()
            ->where('field', $validated['field'])
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Une suggestion est déjà en attente pour ce champ.'], 422);
        }

        $suggestion = $target->modificationSuggestions()->create([
            'user_id' => Auth::id(),
            'field' => $validated['field'],
            'suggested_value' => $validated['suggested_value'],
            'status' => 'pending',
            'upvotes_count' => 1, // L'auteur vote d'office pour sa suggestion
        ]);

        // Enregistrer le vote de l'auteur
        ModificationSuggestionVote::create([
            'modification_suggestion_id' => $suggestion->id,
            'user_id' => Auth::id(),
            'is_upvote' => true,
        ]);

        return response()->json([
            'message' => 'Suggestion soumise avec succès.',
            'suggestion' => $suggestion
        ]);
    }

    /**
     * Voter pour ou contre une suggestion existante.
     */
    public function vote(Request $request, ModificationSuggestion $suggestion)
    {
        $validated = $request->validate([
            'is_upvote' => 'required|boolean',
        ]);

        if ($suggestion->status !== 'pending') {
            return response()->json(['message' => 'Cette suggestion est déjà traitée.'], 422);
        }

        $userId = Auth::id();

        $existingVote = ModificationSuggestionVote::where('modification_suggestion_id', $suggestion->id)
            ->where('user_id', $userId)
            ->first();

        if ($existingVote) {
            if ($existingVote->is_upvote === $validated['is_upvote']) {
                return response()->json(['message' => 'Vous avez déjà voté ce choix.'], 422);
            }

            // Inverser le vote
            $existingVote->update(['is_upvote' => $validated['is_upvote']]);
            
            if ($validated['is_upvote']) {
                $suggestion->increment('upvotes_count');
                $suggestion->decrement('downvotes_count');
            } else {
                $suggestion->decrement('upvotes_count');
                $suggestion->increment('downvotes_count');
            }

            return response()->json(['message' => 'Vote mis à jour.']);
        }

        // Nouveau vote
        ModificationSuggestionVote::create([
            'modification_suggestion_id' => $suggestion->id,
            'user_id' => $userId,
            'is_upvote' => $validated['is_upvote'],
        ]);

        if ($validated['is_upvote']) {
            $suggestion->increment('upvotes_count');
        } else {
            $suggestion->increment('downvotes_count');
        }

        // Optionnel : On pourrait déclencher le job de vérification ici :
        // ProcessModificationSuggestions::dispatchSync();

        return response()->json(['message' => 'A voté.']);
    }
}
