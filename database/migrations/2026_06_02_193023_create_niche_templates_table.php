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
        Schema::create('niche_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_niche_id')->constrained()->cascadeOnDelete();
            // Labels dinâmicos por nicho (customer, appointment, service, resource)
            $table->json('labels');
            // Campos extras por entidade
            $table->json('custom_fields')->nullable();
            // Status padrão do nicho
            $table->json('default_statuses');
            // Serviços sugeridos
            $table->json('default_services')->nullable();
            // Automações padrão
            $table->json('automations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('niche_templates');
    }
};
