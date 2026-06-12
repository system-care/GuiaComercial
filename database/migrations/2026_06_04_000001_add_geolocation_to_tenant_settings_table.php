<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_settings', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('settings');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('city_normalized', 100)->nullable()->after('longitude');
            $table->string('neighborhood_normalized', 100)->nullable()->after('city_normalized');
            $table->unsignedSmallInteger('service_radius_km')->nullable()->after('neighborhood_normalized');
            $table->string('service_mode', 20)->nullable()->after('service_radius_km');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_settings', function (Blueprint $table) {
            $table->dropColumn([
                'latitude',
                'longitude',
                'city_normalized',
                'neighborhood_normalized',
                'service_radius_km',
                'service_mode',
            ]);
        });
    }
};
