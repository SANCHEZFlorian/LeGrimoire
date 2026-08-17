<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('readings', function (Blueprint $table) {
            $table->boolean('is_favorite')->default(false);
            $table->text('favorite_quote')->nullable();
            $table->text('favorite_moment')->nullable();
            $table->boolean('is_bought')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('readings', function (Blueprint $table) {
            $table->dropColumn(['is_favorite', 'favorite_quote', 'favorite_moment', 'is_bought']);
        });
    }
};
