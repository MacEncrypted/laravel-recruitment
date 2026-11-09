<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        // Każda firma ma jednego Kapitana i kilku członków na start.
        // Steve Rogers celowo pokazuje wszystkie trzy role: Kapitan (Stark), członek (Wayne), spoza firmy (E.S.W.A.T).
        $companies = [
            'Stark Industries' => [
                'captain' => 'captainamerica@avengers.com',
                'members' => [
                    'ironman@starkindustries.com',
                    'hulk@avengers.com',
                    'spiderman@dailybugle.com',
                ],
            ],
            'Wayne Enterprises' => [
                'captain' => 'blackwidow@shield.gov',
                'members' => [
                    'captainamerica@avengers.com',
                    'hawkeye@shield.gov',
                    'thor@asgard.com',
                ],
            ],
            'E.S.W.A.T' => [
                'captain' => 'scarletwitch@avengers.com',
                'members' => [
                    'blackwidow@shield.gov',
                    'thor@asgard.com',
                ],
            ],
        ];

        foreach ($companies as $name => $setup) {
            $company = Company::firstOrCreate(['name' => $name]);

            $captain = User::where('email', $setup['captain'])->firstOrFail();
            $memberIds = User::whereIn('email', $setup['members'])->pluck('id');

            // Członkowie bez flagi, Kapitan z flagą – sync gwarantuje jednego Kapitana na firmę
            $pivot = $memberIds->mapWithKeys(fn ($id) => [$id => ['is_captain' => false]])->all();
            $pivot[$captain->id] = ['is_captain' => true];
            $company->users()->sync($pivot);

            $this->command->info("🏢 {$name}: captain {$captain->name}, " . count($pivot) . ' users');
        }
    }
}
