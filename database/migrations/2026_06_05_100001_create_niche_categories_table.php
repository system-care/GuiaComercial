<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('niche_categories', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::table('business_niches', function (Blueprint $table) {
            $table->foreignId('niche_category_id')
                ->nullable()
                ->after('id')
                ->constrained('niche_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('business_niches', function (Blueprint $table) {
            $table->dropForeign(['niche_category_id']);
            $table->dropColumn('niche_category_id');
        });

        Schema::dropIfExists('niche_categories');
    }
};
