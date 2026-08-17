<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Faq;
use App\Models\SupportTicket;
use App\Models\FeatureSuggestion;
use App\Models\FeatureSuggestionVote;

class SupportController extends Controller
{
    public function faqs(Request $request)
    {
        return response()->json(Faq::where('is_active', true)->orderBy('order')->get());
    }

    public function submitTicket(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string'
        ]);

        $ticket = SupportTicket::create([
            'user_id' => $request->user()->id,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
        ]);

        return response()->json(['message' => 'Ticket envoyé', 'ticket' => $ticket], 201);
    }

    public function suggestions(Request $request)
    {
        $suggestions = FeatureSuggestion::with('user:id,name')
            ->withExists(['votes as has_voted' => function ($query) use ($request) {
                $query->where('user_id', $request->user()?->id ?: 0);
            }])
            ->orderByDesc('upvotes_count')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($suggestions);
    }

    public function submitSuggestion(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string'
        ]);

        $suggestion = FeatureSuggestion::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
        ]);

        return response()->json(['message' => 'Suggestion enregistrée', 'suggestion' => $suggestion], 201);
    }

    public function voteSuggestion(Request $request, FeatureSuggestion $suggestion)
    {
        $userId = $request->user()->id;
        
        $vote = FeatureSuggestionVote::where('user_id', $userId)
            ->where('feature_suggestion_id', $suggestion->id)
            ->first();

        if ($vote) {
            // Unvote
            $vote->delete();
            $suggestion->decrement('upvotes_count');
            return response()->json(['message' => 'Vote retiré', 'voted' => false]);
        } else {
            // Vote
            FeatureSuggestionVote::create([
                'user_id' => $userId,
                'feature_suggestion_id' => $suggestion->id
            ]);
            $suggestion->increment('upvotes_count');
            return response()->json(['message' => 'Vote enregistré', 'voted' => true]);
        }
    }
}
