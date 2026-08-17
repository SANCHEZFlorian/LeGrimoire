<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('readings', function (Blueprint $table) {
            $table->unsignedBigInteger('book_id')->nullable()->change();
        });

        if (Schema::hasTable('custom_awards')) {
            Schema::table('custom_awards', function (Blueprint $table) {
                $table->unsignedBigInteger('book_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('readings', function (Blueprint $table) {
            $table->unsignedBigInteger('book_id')->nullable(false)->change();
        });

        if (Schema::hasTable('custom_awards')) {
            Schema::table('custom_awards', function (Blueprint $table) {
                $table->unsignedBigInteger('book_id')->nullable(false)->change();
            });
        }
    }
};
