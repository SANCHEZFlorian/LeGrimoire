<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index()
    {
        return Book::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'pages_count' => 'nullable|integer',
            'publication_date' => 'nullable|date',
            'cover_url' => 'nullable|url',
            'series_name' => 'nullable|string|max:255',
            'series_number' => 'nullable|string|max:255',
            'genre' => 'nullable|string|max:255',
            'sub_genre' => 'nullable|string|max:255',
            'summary' => 'nullable|string',
            'audience' => 'nullable|string|max:255',
        ]);

        $book = Book::create($validated);
        return response()->json($book, 201);
    }

    public function show(Book $book)
    {
        return $book;
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'author' => 'sometimes|required|string|max:255',
            'isbn' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'pages_count' => 'nullable|integer',
            'publication_date' => 'nullable|date',
            'cover_url' => 'nullable|url',
            'series_name' => 'nullable|string|max:255',
            'series_number' => 'nullable|string|max:255',
            'genre' => 'nullable|string|max:255',
            'sub_genre' => 'nullable|string|max:255',
            'summary' => 'nullable|string',
            'audience' => 'nullable|string|max:255',
        ]);

        $book->update($validated);
        return response()->json($book);
    }

    public function destroy(Book $book)
    {
        $book->delete();
        return response()->json(null, 204);
    }
}
