<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('readings', function (Blueprint $table) {
            $table->unsignedTinyInteger('spice_rating')->nullable()->after('rating');
            $table->integer('reading_time_minutes')->nullable()->after('language');
            $table->string('listening_hours')->nullable()->after('reading_time_minutes');
            $table->boolean('is_reread')->default(false)->after('is_bought');
            $table->boolean('is_dnf')->default(false)->after('is_reread');
            $table->string('story_type')->nullable()->after('tropes');
        });
    }

    public function down(): void
    {
        Schema::table('readings', function (Blueprint $table) {
            $table->dropColumn([
                'spice_rating',
                'reading_time_minutes',
                'listening_hours',
                'is_reread',
                'is_dnf',
                'story_type',
            ]);
        });
    }
};
