<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->json('business_niche_ids')->nullable()->after('business_niche_id');
        });

        Schema::table('registration_otps', function (Blueprint $table) {
            $table->json('business_niche_ids')->nullable()->after('business_niche_id');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('business_niche_ids');
        });

        Schema::table('registration_otps', function (Blueprint $table) {
            $table->dropColumn('business_niche_ids');
        });
    }
};
