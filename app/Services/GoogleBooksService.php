<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleBooksService
{
    /**
     * Rechercher des livres via l'API Google Books.
     *
     * @param string $query
     * @param string|null $lang
     * @return array
     */
    public function searchBooks(string $query, ?string $lang = 'fr'): array
    {
        $apiKey = config('services.google_books.key') ?? env('GOOGLE_BOOKS_API_KEY');
        
        $params = [
            'q' => $query,
            'maxResults' => 30,
            'printType' => 'books',
        ];

        if (!empty($apiKey)) {
            $params['key'] = $apiKey;
        }

        if (!empty($lang) && $lang !== 'all') {
            $params['langRestrict'] = $lang;
        }

        try {
            $http = Http::timeout(10)
                ->retry(2, 200, throw: false)
                ->withHeaders([
                    'User-Agent' => 'CarnetLecture/1.0 (Laravel)',
                ]);

            $verifySsl = config('services.google_books.verify_ssl', false);
            if (!$verifySsl) {
                $http = $http->withoutVerifying();
            }

            $response = $http->get('https://www.googleapis.com/books/v1/volumes', $params);

            if ($response->failed()) {
                Log::warning('Google Books API failed response', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [];
            }

            $items = $response->json('items') ?? [];
        } catch (\Throwable $e) {
            Log::error('Google Books API connection error: ' . $e->getMessage());
            return [];
        }
        $itemsCollection = collect($items);

        // Filtre strict sur la langue si spécifiée
        if (!empty($lang) && $lang !== 'all') {
            $itemsCollection = $itemsCollection->filter(function ($item) use ($lang) {
                return isset($item['volumeInfo']['language']) && 
                       str_starts_with(strtolower($item['volumeInfo']['language']), strtolower($lang));
            });
        }

        $items = $itemsCollection->take(15)->values()->all();
        $books = [];

        foreach ($items as $item) {
            $volumeInfo = $item['volumeInfo'] ?? [];
            
            if (empty($volumeInfo['title'])) {
                continue;
            }
            
            // Extraction de l'ISBN
            $identifiers = $volumeInfo['industryIdentifiers'] ?? [];
            $isbn = null;
            foreach ($identifiers as $identifier) {
                if ($identifier['type'] === 'ISBN_13') {
                    $isbn = $identifier['identifier'];
                    break;
                } elseif ($identifier['type'] === 'ISBN_10') {
                    $isbn = $identifier['identifier'];
                }
            }

            // Gestion de l'image de couverture
            $imageLinks = $volumeInfo['imageLinks'] ?? [];
            $coverUrl = $imageLinks['thumbnail'] ?? $imageLinks['smallThumbnail'] ?? null;
            
            if ($coverUrl) {
                $coverUrl = str_replace('http://', 'https://', $coverUrl);
                $coverUrl = str_replace('&edge=curl', '', $coverUrl);
            }

            // Auteurs
            $authors = isset($volumeInfo['authors']) ? implode(', ', $volumeInfo['authors']) : 'Auteur inconnu';
            
            // Date de parution
            $publishedDate = $volumeInfo['publishedDate'] ?? null;
            $pubYear = null;
            if ($publishedDate && strlen($publishedDate) === 4) {
                $pubYear = (int) $publishedDate;
                $publishedDate = $publishedDate . '-01-01';
            } elseif ($publishedDate && strlen($publishedDate) === 7) {
                $pubYear = (int) substr($publishedDate, 0, 4);
                $publishedDate = $publishedDate . '-01';
            } elseif ($publishedDate && strlen($publishedDate) >= 10) {
                $pubYear = (int) substr($publishedDate, 0, 4);
                $publishedDate = substr($publishedDate, 0, 10);
            }

            // Catégories / Genres multiples
            $rawCategories = $volumeInfo['categories'] ?? [];
            $genres = [];
            foreach ($rawCategories as $cat) {
                // Split categories like "Fiction / Fantasy / Epic"
                $subCats = preg_split('/[\/|,;]+/', $cat);
                foreach ($subCats as $sc) {
                    $cleaned = trim($sc);
                    if ($cleaned !== '' && !in_array($cleaned, $genres)) {
                        $genres[] = $cleaned;
                    }
                }
            }

            // Détection du format approximatif selon printType / isEbook
            $format = 'Broché';
            if (isset($item['saleInfo']['isEbook']) && $item['saleInfo']['isEbook']) {
                $format = 'Ebook';
            }

            $books[] = [
                'google_id' => $item['id'],
                'title' => $volumeInfo['title'] ?? 'Titre inconnu',
                'original_title' => null,
                'author' => $authors,
                'genres' => $genres,
                'isbn' => $isbn,
                'publisher' => $volumeInfo['publisher'] ?? null,
                'format' => $format,
                'default_pages_count' => $volumeInfo['pageCount'] ?? null,
                'publication_date' => $publishedDate,
                'first_publication_year' => $pubYear,
                'cover_url' => $coverUrl,
                'summary' => $volumeInfo['description'] ?? null,
                'language' => $volumeInfo['language'] ?? 'fr',
            ];
        }

        return $books;
    }
}
