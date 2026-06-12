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
        Schema::create('business_niches', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Adicionar FK em tenants agora que business_niches existe
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreign('business_niche_id')->references('id')->on('business_niches')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['business_niche_id']);
        });
        Schema::dropIfExists('business_niches');
    }
};
