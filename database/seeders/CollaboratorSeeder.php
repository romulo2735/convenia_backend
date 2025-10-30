<?php

namespace Database\Seeders;

use App\Models\Collaborator;
use App\Models\User;
use Illuminate\Database\Seeder;

class CollaboratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (User::query()->count() === 0) {
            User::factory()->count(5)->create();
        }

        User::query()->each(function (User $user): void {
            Collaborator::factory()->count(20)->create([
                'user_id' => $user->id,
            ]);
        });
    }
}
