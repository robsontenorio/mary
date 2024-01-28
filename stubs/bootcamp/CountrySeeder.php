<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Avoid duplicates
        if (Country::count() > 0) {
            return;
        }

        Country::insert([
            [
                'name' => 'Brazil'
            ],
            [
                'name' => 'India'
            ],
            [
                'name' => 'United States'
            ],
            [
                'name' => 'France'
            ],
        ]);
    }
}
