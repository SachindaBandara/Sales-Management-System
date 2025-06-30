<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
          // Create default admin user
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Create default customer user
        User::factory()->create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
        ]);

        // Create additional test users
        User::factory(10)->create(); // 10 customers
        User::factory(2)->admin()->create(); // 2 additional admins
        User::factory(3)->inactive()->create(); // 3 inactive customers
    }
}
