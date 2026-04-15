<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\EconomicActivitySeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\MunicipalitySeeder;
use Database\Seeders\CountrySeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            CountrySeeder::class,
        ]);
    }
}
