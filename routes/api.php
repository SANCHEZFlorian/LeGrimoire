<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\ReadingController;
use App\Http\Controllers\Api\CustomAwardController;
use App\Http\Controllers\Api\BookSearchController;
use App\Http\Controllers\Api\ChronicleController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ThemeController;
use App\Http\Controllers\Api\UserPreferencesController;
use App\Http\Controllers\ModificationSuggestionController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/theme', [ThemeController::class, 'getTheme']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/chronicle', [ChronicleController::class, 'index']);
    // Catalog ("THE bibliothèque")
    Route::get('/catalog/works', [CatalogController::class, 'index']);
    Route::get('/catalog/works/{work}', [CatalogController::class, 'show']);
    Route::post('/catalog/works', [CatalogController::class, 'store']);
    Route::put('/catalog/works/{work}', [CatalogController::class, 'updateWork']);
    Route::delete('/catalog/works/{work}', [CatalogController::class, 'destroyWork']);

    Route::post('/catalog/works/{work}/editions', [CatalogController::class, 'addEdition']);
    Route::put('/catalog/editions/{edition}', [CatalogController::class, 'updateEdition']);
    Route::post('/catalog/editions/{edition}', [CatalogController::class, 'updateEdition']);
    Route::delete('/catalog/editions/{edition}', [CatalogController::class, 'destroyEdition']);

    Route::post('/modification-suggestions', [ModificationSuggestionController::class, 'store']);
    Route::post('/modification-suggestions/{suggestion}/vote', [ModificationSuggestionController::class, 'vote']);

    Route::get('/catalog/genres', [CatalogController::class, 'genres']);

    Route::post('/theme', [ThemeController::class, 'updateTheme']);

    Route::get('/user/preferences', [UserPreferencesController::class, 'show']);
    Route::put('/user/preferences', [UserPreferencesController::class, 'update']);

    Route::get('/export/month/{year}/{month}', [App\Http\Controllers\Api\ExportController::class, 'exportMonthPdf']);

    Route::apiResource('books', BookController::class);
    Route::apiResource('readings', ReadingController::class);
    Route::apiResource('custom-awards', CustomAwardController::class);

    // Support & Boîte à idées
    Route::get('/support/faq', [App\Http\Controllers\Api\SupportController::class, 'faqs']);
    Route::post('/support/tickets', [App\Http\Controllers\Api\SupportController::class, 'submitTicket']);
    Route::get('/support/suggestions', [App\Http\Controllers\Api\SupportController::class, 'suggestions']);
    Route::post('/support/suggestions', [App\Http\Controllers\Api\SupportController::class, 'submitSuggestion']);
    Route::post('/support/suggestions/{suggestion}/vote', [App\Http\Controllers\Api\SupportController::class, 'voteSuggestion']);

    // Admin Routes
    Route::prefix('admin')->middleware('role:admin,moderator,librarian')->group(function () {
        // Placeholder routes for admin dashboard
        Route::get('/dashboard', function () {
            return response()->json(['message' => 'Admin dashboard data']);
        });

        // Specifc roles
        Route::middleware('role:admin')->group(function () {
            Route::get('/users', function () { return response()->json(['message' => 'Users list']); });
        });

        Route::middleware('role:admin,moderator')->group(function () {
            Route::get('/moderation', function () { return response()->json(['message' => 'Moderation data']); });
        });

        Route::middleware('role:admin,librarian')->group(function () {
            Route::get('/catalog', function () { return response()->json(['message' => 'Catalog data']); });
        });
    });
});
