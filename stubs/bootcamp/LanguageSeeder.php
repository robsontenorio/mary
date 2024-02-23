<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        // Avoid duplicates
        if (Language::count() > 0) {
            return;
        }

        Language::insert([
            ['name' => 'English'],
            ['name' => 'French'],
            ['name' => 'Portuguese'],
            ['name' => 'Hindi']
        ]);
    }
}
