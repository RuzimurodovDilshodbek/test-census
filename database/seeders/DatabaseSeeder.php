<?php

namespace Database\Seeders;

use App\Models\ApplicationDenyReason;
use Database\Seeders\ApplicationDenyReasonsSeeder;
use Database\Seeders\CitiesTableSeeder;
use Illuminate\Database\Seeder;
use Database\Seeders\RegionsTableSeeder;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // User::factory(10)->create();
        $this->call(ApplicationDenyReasonsSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(RegionsTableSeeder::class);
        $this->call(CitiesTableSeeder::class);
        $this->call(SocialAreasTableSeeder::class);
        $this->call(CitizensTableSeeder::class);


    }
}
