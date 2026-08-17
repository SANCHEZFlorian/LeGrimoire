<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomAward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomAwardController extends Controller
{
    public function index()
    {
        return Auth::user()->customAwards()->with('book')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'name' => 'required|string|max:255',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer',
        ]);

        $award = Auth::user()->customAwards()->create($validated);
        return response()->json($award->load('book'), 201);
    }

    public function show(CustomAward $customAward)
    {
        if ($customAward->user_id !== Auth::id()) {
            abort(403);
        }
        return $customAward->load('book');
    }

    public function update(Request $request, CustomAward $customAward)
    {
        if ($customAward->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer',
        ]);

        $customAward->update($validated);
        return response()->json($customAward->load('book'));
    }

    public function destroy(CustomAward $customAward)
    {
        if ($customAward->user_id !== Auth::id()) {
            abort(403);
        }

        $customAward->delete();
        return response()->json(null, 204);
    }
}
