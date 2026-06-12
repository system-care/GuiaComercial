<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('recurrence_group_id', 36)->nullable()->after('custom_data')->index();
            $table->tinyInteger('recurrence_index')->nullable()->after('recurrence_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['recurrence_group_id']);
            $table->dropColumn(['recurrence_group_id', 'recurrence_index']);
        });
    }
};
