<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_otps', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('gestor_name');
            $table->string('email')->index();
            $table->string('phone', 20);
            $table->string('password');
            $table->foreignId('business_niche_id')->constrained('business_niches');
            $table->char('code', 6);
            $table->string('ip', 45)->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_otps');
    }
};
