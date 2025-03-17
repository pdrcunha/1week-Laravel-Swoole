<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;
use App\Models\Product;

class BootStrapDev extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ...existing code...

        Company::factory(20)->create()->each(function ($company) {
            User::factory(20)->create(['company_id' => $company->id]);
            Product::factory(100)->create(['company_id' => $company->id]);
        });
    }
}