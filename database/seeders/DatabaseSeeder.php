<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CashSeeder::class);
        $this->call(CountrySeeder::class);
        $this->call(DepartmentSeeder::class);
        $this->call(ProvinceSeeder::class);
        $this->call(DistrictSeeder::class);
        $this->call(IdentityDocumentTypeSeeder::class);
        $this->call(IgvTypeAffectionSeeder::class);
        $this->call(CurrencySeeder::class);
        $this->call(CreditNoteTypeSeeder::class);
        $this->call(DebitNoteTypeSeeder::class);
        $this->call(ClientSeeder::class);
        $this->call(BusineSeeder::class);
        $this->call(WarehouseSeeder::class);
        $this->call(ArchingCashSeeder::class);
        $this->call(TypeDocumentSeeder::class);
        $this->call(SerieSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(UnitSeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(PayModeSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(ClienteRoleSeeder::class);
    }
}
