<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('works', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('original_title')->nullable();
            $table->string('author');
            $table->json('genres')->nullable();
            $table->string('sub_genre')->nullable();
            $table->string('series_name')->nullable();
            $table->string('series_number')->nullable();
            $table->string('audience')->nullable();
            $table->integer('first_publication_year')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['title', 'author']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('works');
    }
};
