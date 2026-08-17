<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('editions', function (Blueprint $table) {
            $table->dropColumn(['default_pages_count', 'default_audio_duration']);
        });
    }

    public function down(): void
    {
        Schema::table('editions', function (Blueprint $table) {
            $table->integer('default_pages_count')->nullable();
            $table->string('default_audio_duration')->nullable();
        });
    }
};
