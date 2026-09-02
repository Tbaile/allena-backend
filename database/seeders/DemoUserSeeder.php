<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUser('Allena Expert', 'expert@allena.app', 'expert');
        $this->seedUser('Allena Customer', 'customer@allena.app', 'client', true);
    }

    private function seedUser(string $name, string $email, string $role, bool $mustChangePassword = false): void
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'password',
                'must_change_password' => $mustChangePassword,
            ]
        );

        if (! $user->hasRole($role)) {
            $user->assignRole($role);
        }
    }
}
