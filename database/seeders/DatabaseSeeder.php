<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
     
        $company = Company::create([
            'name' => 'Administradora',
            'cnpj' => '12345678000100',
            'email' => 'admin@company.com'
        ]);
  
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@user.com',
            'password' => 'password',
            'company_id' => $company->id
        ]);
    }
}
