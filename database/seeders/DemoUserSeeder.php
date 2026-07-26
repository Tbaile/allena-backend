<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUser('Fairly Expert', 'expert@fairly.app', 'expert');
        $this->seedUser('Fairly Customer', 'customer@fairly.app', 'client');
    }

    private function seedUser(string $name, string $email, string $role): void
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'password',
                'must_change_password' => false,
            ]
        );

        if (! $user->hasRole($role)) {
            $user->assignRole($role);
        }
    }
}
