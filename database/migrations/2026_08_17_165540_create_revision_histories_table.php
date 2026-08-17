<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revision_histories', function (Blueprint $table) {
            $table->id();
            $table->morphs('auditable'); // auditable_type, auditable_id
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // User who triggered the revision
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revision_histories');
    }
};
