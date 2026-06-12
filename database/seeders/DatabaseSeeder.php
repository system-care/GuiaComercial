<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'adwilsonferreira@gmail.com'],
            [
                'name'     => 'Wilson Ferreira',
                'password' => bcrypt('Adfer1972#'),
                'role'     => 'super_admin',
            ]
        );

        $this->call([
            NicheSeeder::class,
            PlanSeeder::class,
            DemoSeeder::class,
        ]);
    }
}
