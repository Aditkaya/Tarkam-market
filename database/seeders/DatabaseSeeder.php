<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }
        User::firstOrCreate(['email' => 'admin@tarkam.id'], [
            'name' => 'Admin Tarkam',
            'password' => 'Tarkam123!',
        ]);
    }
}
