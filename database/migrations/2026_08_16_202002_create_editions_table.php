<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('editions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id')->constrained('works')->cascadeOnDelete();
            $table->string('format')->nullable()->default('Broché'); // Broché, Relié, Poche, Ebook, Audio...
            $table->string('publisher')->nullable();
            $table->date('publication_date')->nullable();
            $table->string('isbn')->nullable();
            $table->string('cover_url', 1000)->nullable();
            $table->string('spine_color')->nullable()->default('#8B4513');
            $table->text('summary')->nullable();
            $table->string('language')->nullable()->default('fr');
            $table->integer('default_pages_count')->nullable();
            $table->string('default_audio_duration')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('isbn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('editions');
    }
};
