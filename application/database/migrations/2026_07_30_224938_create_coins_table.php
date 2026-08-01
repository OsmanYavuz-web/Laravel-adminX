<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('find_id')->constrained()->cascadeOnDelete();
            $table->foreignId('excavation_project_id')->constrained()->cascadeOnDelete();

            // Sözlük ilişkileri (Dictionary FK'lar)
            $table->foreignId('period_id')->nullable()->constrained('dictionaries')->nullOnDelete();
            $table->foreignId('authority_id')->nullable()->constrained('dictionaries')->nullOnDelete();
            $table->foreignId('ruler_id')->nullable()->constrained('dictionaries')->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('dictionaries')->nullOnDelete();
            $table->foreignId('mint_id')->nullable()->constrained('dictionaries')->nullOnDelete();
            $table->foreignId('metal_id')->nullable()->constrained('dictionaries')->nullOnDelete();
            $table->foreignId('denomination_id')->nullable()->constrained('dictionaries')->nullOnDelete();

            // Tarih
            $table->string('date_range')->nullable(); // "MÖ 2 - MÖ 1"

            // Fiziksel özellikler
            $table->decimal('diameter', 6, 2)->nullable(); // mm
            $table->decimal('weight', 8, 3)->nullable();   // gram
            $table->integer('axis')->nullable();           // Kalıp yönü (1-12, saat yönü)
            $table->boolean('is_cut')->default(false);     // Kesilmiş / Kırpılmış
            $table->boolean('is_pierced')->default(false); // Delinmiş

            // Ön Yüz (Obverse)
            $table->text('obverse_description')->nullable();
            $table->string('obverse_legend')->nullable();
            $table->string('obverse_legend_expanded')->nullable();

            // Arka Yüz (Reverse)
            $table->text('reverse_description')->nullable();
            $table->string('reverse_legend')->nullable();
            $table->string('reverse_legend_expanded')->nullable();

            // Ekstra tanımlayıcılar
            $table->string('mint_mark')->nullable();       // Darphane İşareti
            $table->string('magistrate')->nullable();      // Magistrat / Yetkili
            $table->string('control_mark')->nullable();    // Kontrol İşareti
            $table->string('monogram')->nullable();        // Monogram
            $table->string('countermark')->nullable();     // Kontrmark
            $table->boolean('is_overstrike')->default(false); // Üst Baskı

            // Referans ve notlar
            $table->text('reference')->nullable();         // Katalog referansı
            $table->text('note')->nullable();              // Açıklama / kondisyon

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['excavation_project_id', 'find_id']);
            $table->index('period_id');
            $table->index('metal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coins');
    }
};
