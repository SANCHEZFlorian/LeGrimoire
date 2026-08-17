<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modification_suggestions', function (Blueprint $table) {
            $table->id();
            $table->morphs('target'); // target_type, target_id
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('field');
            $table->text('suggested_value')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected, manual_review
            $table->integer('upvotes_count')->default(0);
            $table->integer('downvotes_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modification_suggestions');
    }
};
