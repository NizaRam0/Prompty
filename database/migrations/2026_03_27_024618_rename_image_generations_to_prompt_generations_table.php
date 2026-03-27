<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('image_generations') && !Schema::hasTable('prompt_generations')) {
            Schema::rename('image_generations', 'prompt_generations');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('prompt_generations') && !Schema::hasTable('image_generations')) {
            Schema::rename('prompt_generations', 'image_generations');
        }
    }
};
