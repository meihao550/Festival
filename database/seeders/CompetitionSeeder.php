<?php

namespace Database\Seeders;

use App\Models\Competition;
use Illuminate\Database\Seeder;

class CompetitionSeeder extends Seeder
{
    public function run(): void
    {
        $competitions = ['玉入れ', '綱引き', 'リレー'];

        foreach ($competitions as $name) {
            Competition::firstOrCreate(['name' => $name]);
        }
    }
}
