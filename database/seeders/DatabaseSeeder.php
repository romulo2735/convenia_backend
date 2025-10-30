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
        // Base user for testing/demo
        User::factory()->create([
            'name' => 'Convenia Teste User',
            'email' => 'convenia@email.com',
        ]);

        // Additional users
        $this->call(UserSeeder::class);

        // Collaborators for all users
        $this->call(CollaboratorSeeder::class);
    }
}
