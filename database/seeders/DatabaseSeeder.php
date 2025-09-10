<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $callableSeeds = App::environment('local')
            ? array_merge($this->getProductionSeeders(), $this->getDevelopmentSeeders())
            : $this->getProductionSeeders();

        $this->call($callableSeeds);
    }

    protected function getProductionSeeders(): array
    {
        return [
            RoleSeeder::class,
        ];
    }

    protected function getDevelopmentSeeders(): array
    {
        return [
            UserSeeder::class,
            ProjectSeeder::class,
            TaskSeeder::class,
        ];
    }
}
