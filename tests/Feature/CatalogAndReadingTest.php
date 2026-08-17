<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Work;
use App\Models\Edition;
use App\Models\Reading;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogAndReadingTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_can_create_work_and_edition_and_add_to_library(): void
    {
        $user = User::factory()->create();

        // 1. Créer une œuvre avec plusieurs genres et une 1ère édition
        $response = $this->actingAs($user)->postJson('/api/catalog/works', [
            'title' => 'Eragon Test',
            'author' => 'Christopher Paolini',
            'genres' => ['Fantasy', 'Jeunesse', 'Aventure', 'Dragons'],
            'series_name' => "L'Héritage",
            'series_number' => '1',
            'audience' => 'Young Adult',
            'edition' => [
                'format' => 'Broché',
                'publisher' => 'Bayard Jeunesse',
                'publication_date' => '2004-10-15',
                'isbn' => '9782747014403',
                'summary' => 'Eragon découvre un œuf de dragon bleu.',
                'default_pages_count' => 680,
            ]
        ]);

        $response->assertStatus(201);
        $workData = $response->json();
        $this->assertEquals('Eragon Test', $workData['title']);
        $this->assertCount(4, $workData['genres']);
        $this->assertCount(1, $workData['editions']);

        $workId = $workData['id'];
        $firstEditionId = $workData['editions'][0]['id'];

        // 2. Ajouter une 2ème édition (Ebook) à cette même œuvre
        $editionResponse = $this->actingAs($user)->postJson("/api/catalog/works/{$workId}/editions", [
            'format' => 'Ebook',
            'publisher' => 'Bayard Numérique',
            'publication_date' => '2011-03-01',
            'isbn' => '9782747038888',
            'summary' => 'Édition numérique d\'Eragon.',
            'default_pages_count' => 520,
        ]);

        $editionResponse->assertStatus(201);
        $secondEditionId = $editionResponse->json('id');

        // 3. L'utilisateur ajoute l'édition Ebook à sa bibliothèque avec un nombre de pages personnalisé (ex: 504 pages sur sa liseuse)
        $readingResponse = $this->actingAs($user)->postJson('/api/readings', [
            'work_id' => $workId,
            'edition_id' => $secondEditionId,
            'format' => 'Numérique',
            'pages_count' => 504, // Page count propre au lecteur
            'status' => 'reading',
            'start_date' => '2026-08-16',
        ]);

        $readingResponse->assertStatus(201);
        $readingData = $readingResponse->json();
        $this->assertEquals(504, $readingData['pages_count']);
        $this->assertEquals('reading', $readingData['status']);
        $this->assertEquals($workId, $readingData['work_id']);
        $this->assertEquals($secondEditionId, $readingData['edition_id']);

        // 4. Vérifier le dashboard
        $dashResponse = $this->actingAs($user)->getJson('/api/dashboard');
        $dashResponse->assertStatus(200);
        $this->assertNotEmpty($dashResponse->json('current_readings'));

        // Nettoyer les données créées par le test
        Reading::where('id', $readingData['id'])->delete();
        Edition::whereIn('id', [$firstEditionId, $secondEditionId])->delete();
        Work::where('id', $workId)->delete();
        $user->delete();
    }
}
