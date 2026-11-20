<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $user = fn (string $email) => User::where('email', $email)->firstOrFail();

        $stark = Company::where('name', 'Stark Industries')->firstOrFail();
        $steve = $user('captainamerica@avengers.com');

        $shield = $stark->items()->firstOrCreate(['name' => "Captain's Shield"], ['quantity' => 3]);
        if (! $shield->assignments()->exists()) {
            $shield->assignTo($user('hulk@avengers.com'), 1, $steve)
                ->reassignTo($user('ironman@starkindustries.com'), $steve);
            $shield->assignTo($user('spiderman@dailybugle.com'), 1, $steve);
        }
        $stark->items()->firstOrCreate(['name' => 'Arc Reactor'], ['quantity' => 2]);

        $wayne = Company::where('name', 'Wayne Enterprises')->firstOrFail();
        $natasha = $user('blackwidow@shield.gov');

        $batarang = $wayne->items()->firstOrCreate(['name' => 'Batarang'], ['quantity' => 10]);
        if (! $batarang->assignments()->exists()) {
            $batarang->assignTo($steve, 2, $natasha);
            $batarang->assignTo($user('hawkeye@shield.gov'), 3, $natasha);
        }

        $this->command->info('📦 Warehouse: demo items and assignments');
    }
}
