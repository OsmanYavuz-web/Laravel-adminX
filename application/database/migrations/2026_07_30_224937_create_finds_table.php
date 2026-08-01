<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('excavation_project_id')->constrained()->cascadeOnDelete();

            // Zorunlu alanlar
            $table->date('find_date');
            $table->string('inventory_number');
            $table->string('excavation_area'); // Kazı Alanı

            // Kazı Bağlamı
            $table->string('excavation_season')->nullable();
            $table->string('sector')->nullable();
            $table->string('area')->nullable();
            $table->string('trench')->nullable();          // Açma
            $table->string('square')->nullable();          // Kare / Grid
            $table->string('sub_square')->nullable();      // Alt Kare
            $table->string('locus')->nullable();
            $table->string('context')->nullable();         // Konteks
            $table->string('stratigraphic_unit')->nullable(); // SU
            $table->string('unit')->nullable();
            $table->string('layer')->nullable();           // Tabaka
            $table->string('level')->nullable();           // Seviye
            $table->string('phase')->nullable();           // Evre
            $table->string('feature')->nullable();         // Feature / Özellik
            $table->string('grave_number')->nullable();    // Mezar Numarası
            $table->string('structure')->nullable();       // Yapı
            $table->string('room')->nullable();            // Mekân
            $table->string('architectural_feature')->nullable(); // Mimari Unsur

            // Konum
            $table->string('find_spot')->nullable();      // Buluntu Yeri
            $table->decimal('elevation', 8, 2)->nullable(); // Kot (metre)
            $table->decimal('coordinate_x', 10, 4)->nullable();
            $table->decimal('coordinate_y', 10, 4)->nullable();
            $table->decimal('coordinate_z', 10, 4)->nullable();

            // Numaralandırma
            $table->string('find_number')->nullable();    // Buluntu Numarası
            $table->string('bag_number')->nullable();     // Torba Numarası

            // Ek
            $table->string('find_group')->nullable();     // Buluntu Grubu
            $table->text('find_note')->nullable();        // Buluntu Notu

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // Envanter numarası proje içinde unique
            $table->unique(['excavation_project_id', 'inventory_number']);
            $table->index(['excavation_project_id', 'find_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finds');
    }
};
