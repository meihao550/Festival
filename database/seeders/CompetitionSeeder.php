<?php

namespace Database\Seeders;

use App\Models\Competition;
use Illuminate\Database\Seeder;

class CompetitionSeeder extends Seeder
{
    public function run(): void
    {
        $competitions = ['モルック', 'Tore', 'VS嵐'];

        foreach ($competitions as $name) {
            Competition::firstOrCreate(['name' => $name]);
        }
    }
}
