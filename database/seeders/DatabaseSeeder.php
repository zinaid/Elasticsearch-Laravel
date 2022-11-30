<?php

namespace Database\Seeders;
use App\Models\Paper;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Paper::factory()->times(50)->create();
    }
}
