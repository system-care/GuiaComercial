<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('billing_cycle', 20)->default('monthly'); // monthly | yearly
            $table->unsignedSmallInteger('trial_days')->default(0);
            $table->unsignedSmallInteger('max_appointments_month')->default(0); // 0 = ilimitado
            $table->unsignedSmallInteger('max_professionals')->default(1);
            $table->unsignedSmallInteger('max_services')->default(5);
            $table->json('features')->nullable(); // lista de features exibidas na landing
            $table->boolean('active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
