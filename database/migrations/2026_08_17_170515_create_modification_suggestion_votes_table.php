<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modification_suggestion_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modification_suggestion_id')->constrained('modification_suggestions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_upvote'); // true = upvote, false = downvote
            $table->timestamps();

            // Un utilisateur ne peut voter qu'une fois pour une même suggestion
            $table->unique(['modification_suggestion_id', 'user_id'], 'mod_sug_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modification_suggestion_votes');
    }
};
