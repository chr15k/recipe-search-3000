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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');

            $table->string('author_email')
                ->index()
                ->comment('Indexed for exact match search optimization');

            $table->json('steps');
            $table->text('steps_text')
                ->nullable()
                ->comment('Additional column to allow full-text search on steps');

            $table->string('image')
                ->nullable()
                ->comment('URL to the recipe image');

            $table->timestamps();

            // Add FULLTEXT index on name, description, and steps_text for search performance
            $table->fullText(['name', 'description', 'steps_text']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
