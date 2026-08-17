<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Book;
use App\Models\Reading;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Utilisateur de Test
        $user = User::factory()->create([
            'name' => 'Lecteur Test',
            'email' => 'test@test.com',
            'password' => Hash::make('password'),
        ]);

        // Création de faux livres
        $book1 = Book::create([
            'google_books_id' => 'g1',
            'title' => 'Dune',
            'author' => 'Frank Herbert',
            'publisher' => 'Robert Laffont',
            'pages_count' => 800,
            'publication_date' => '1965-08-01',
            'cover_url' => 'https://books.google.com/books/publisher/content/images/frontcover/B1hSG45JCX4C?fife=w400-h600&source=gbs_api',
            'isbn' => '9782221111111',
            'spine_color' => '#d97706',
        ]);

        $book2 = Book::create([
            'google_books_id' => 'g2',
            'title' => '1984',
            'author' => 'George Orwell',
            'publisher' => 'Gallimard',
            'pages_count' => 328,
            'publication_date' => '1949-06-08',
            'cover_url' => 'https://books.google.com/books/content/images/frontcover/kotPYEqx7kMC?fife=w400-h600&source=gbs_api',
            'isbn' => '9782070368228',
            'spine_color' => '#1e3a8a',
        ]);

        $book3 = Book::create([
            'google_books_id' => 'g3',
            'title' => 'Le Seigneur des Anneaux',
            'author' => 'J.R.R. Tolkien',
            'publisher' => 'Bourgois',
            'pages_count' => 1200,
            'publication_date' => '1954-07-29',
            'cover_url' => 'https://books.google.com/books/content/images/frontcover/yl4dDQAAQBAJ?fife=w400-h600&source=gbs_api',
            'isbn' => '9782267027006',
            'spine_color' => '#064e3b',
        ]);

        // Création des lectures (pour alimenter le Dashboard)
        
        // Livre en cours (Statut: reading)
        Reading::create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'status' => 'reading',
            'current_page' => 450,
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => null,
            'rating' => null,
            'review' => null,
            'format' => 'paperback',
            'language' => 'fr',
        ]);

        // Livre terminé (Statut: finished) ce mois-ci
        Reading::create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'status' => 'finished',
            'current_page' => 328,
            'start_date' => Carbon::now()->subMonths(1),
            'end_date' => Carbon::now()->subDays(5),
            'rating' => 5,
            'review' => "Un classique intemporel. L'intrigue est toujours aussi glaçante, surtout à notre époque.",
            'format' => 'paperback',
            'language' => 'fr',
        ]);

        // Livre terminé le mois dernier
        Reading::create([
            'user_id' => $user->id,
            'book_id' => $book3->id,
            'status' => 'finished',
            'current_page' => 1200,
            'start_date' => Carbon::now()->subMonths(3),
            'end_date' => Carbon::now()->subMonths(1)->subDays(10),
            'rating' => 4,
            'review' => "Une épopée fantastique qui prend son temps, mais la récompense est au bout du chemin.",
            'format' => 'hardcover',
            'language' => 'fr',
        ]);
    }
}
