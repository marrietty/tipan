<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * The lookup tables (role, gender, specialization, status) are already
     * populated by the schema and are treated as a read-only contract. Only
     * the demo dataset is loaded here.
     */
    public function run(): void
    {
        $this->call(DemoSeeder::class);
    }
}
