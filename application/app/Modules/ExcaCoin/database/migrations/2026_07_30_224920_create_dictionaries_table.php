<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dictionaries', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'period', 'authority', 'ruler', 'region', 'mint', 'metal', 'denomination'
            $table->string('code')->nullable(); // 'AE', 'AR', 'AU' vb. kısa kodlar
            $table->json('name');  // {"tr": "Klasik", "en": "Classical"}
            $table->json('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('type');
            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dictionaries');
    }
};
