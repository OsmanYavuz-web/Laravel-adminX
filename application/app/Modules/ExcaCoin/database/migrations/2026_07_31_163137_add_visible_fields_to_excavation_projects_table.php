<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('excavation_projects', function (Blueprint $table) {
            $table->json('visible_fields')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('excavation_projects', function (Blueprint $table) {
            $table->dropColumn('visible_fields');
        });
    }
};
