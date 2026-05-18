<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@eden.cm'],
            [
                'name'     => 'Administrateur',
                'email'    => 'admin@eden.cm',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
                'actif'    => true,
            ]
        );
    }
}