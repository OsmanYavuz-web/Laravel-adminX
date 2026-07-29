<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Language::firstOrCreate(['code' => 'tr'], [
            'name' => 'Turkish',
            'native_name' => 'Türkçe',
            'flag' => '🇹🇷',
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Language::firstOrCreate(['code' => 'en'], [
            'name' => 'English',
            'native_name' => 'English',
            'flag' => '🇬🇧',
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 2,
        ]);
    }
}
