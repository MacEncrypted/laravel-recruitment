<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AvengersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Tworzy zespół Avengers jako użytkowników testowych.
     * Hasło dla wszystkich: "captain!"
     */
    public function run(): void
    {
        $avengers = [
            [
                'name' => 'Steve Rogers',
                'email' => 'captainamerica@avengers.com',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Tony Stark',
                'email' => 'ironman@starkindustries.com',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Thor Odinson',
                'email' => 'thor@asgard.com',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Bruce Banner',
                'email' => 'hulk@avengers.com',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Natasha Romanoff',
                'email' => 'blackwidow@shield.gov',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Clint Barton',
                'email' => 'hawkeye@shield.gov',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Peter Parker',
                'email' => 'spiderman@dailybugle.com',
                'email_verified_at' => null, // Młody, jeszcze nie zweryfikował emaila
            ],
            [
                'name' => 'Wanda Maximoff',
                'email' => 'scarletwitch@avengers.com',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($avengers as $avenger) {
            if (!User::where('email', $avenger['email'])->exists()) {
                User::create(array_merge($avenger, [
                    'password' => Hash::make('captain!'),
                ]));

                $this->command->info("✅ Created: {$avenger['name']} ({$avenger['email']})");
            } else {
                $this->command->warn("⚠️ Skipped: {$avenger['email']} (already exists)");
            }
        }

        $this->command->info('🦸 Avengers team assembled!');
    }
}
