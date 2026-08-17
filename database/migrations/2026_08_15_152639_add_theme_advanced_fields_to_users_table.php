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
        Schema::table('users', function (Blueprint $table) {
            $table->string('theme_mode')->default('auto')->after('theme_preference'); // auto, fixed, chronicle_auto
            $table->string('theme_name')->default('grimoire')->after('theme_mode');
            $table->string('fixed_season')->nullable()->after('theme_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['theme_mode', 'theme_name', 'fixed_season']);
        });
    }
};
