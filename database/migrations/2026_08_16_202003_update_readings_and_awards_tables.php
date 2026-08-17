<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('readings', function (Blueprint $table) {
            $table->foreignId('work_id')->nullable()->after('book_id')->constrained('works')->nullOnDelete();
            $table->foreignId('edition_id')->nullable()->after('work_id')->constrained('editions')->nullOnDelete();
            $table->integer('pages_count')->nullable()->after('format'); // Nombre de pages personnalisé du lecteur
            $table->string('audio_duration')->nullable()->after('pages_count'); // Durée personnalisée
        });

        if (Schema::hasTable('custom_awards')) {
            Schema::table('custom_awards', function (Blueprint $table) {
                $table->foreignId('work_id')->nullable()->after('book_id')->constrained('works')->nullOnDelete();
                $table->foreignId('edition_id')->nullable()->after('work_id')->constrained('editions')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('readings', function (Blueprint $table) {
            $table->dropForeign(['work_id']);
            $table->dropForeign(['edition_id']);
            $table->dropColumn(['work_id', 'edition_id', 'pages_count', 'audio_duration']);
        });

        if (Schema::hasTable('custom_awards')) {
            Schema::table('custom_awards', function (Blueprint $table) {
                $table->dropForeign(['work_id']);
                $table->dropForeign(['edition_id']);
                $table->dropColumn(['work_id', 'edition_id']);
            });
        }
    }
};
