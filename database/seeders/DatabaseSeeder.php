<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\EmpInfoSeeder;
use Database\Seeders\FingerprintShiftsSeeder;
use Database\Seeders\FingerprintShiftsBSeeder;
use Database\Seeders\Fingerprint24Seeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            EmpInfoSeeder::class,
            FingerprintShiftsSeeder::class,
            FingerprintShiftsBSeeder::class,
            Fingerprint24Seeder::class
        ]);
    }
}
