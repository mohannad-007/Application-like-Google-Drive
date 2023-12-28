<?php

namespace Database\Seeders;

use App\Models\FileEvent;
use Illuminate\Database\Seeder;

class FileEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FileEvent::factory()
            ->count(5)
            ->create();
    }
}
