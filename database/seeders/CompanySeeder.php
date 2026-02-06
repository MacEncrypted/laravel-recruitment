<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            ['name' => 'Stark Industries'],
            ['name' => 'Wayne Enterprises'],
            ['name' => 'E.S.W.A.T'],
        ];

        foreach ($companies as $company) {
            Company::create($company);
        }
    }
}
