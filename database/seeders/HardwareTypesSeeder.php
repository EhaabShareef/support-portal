<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HardwareType;

class HardwareTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['Desktop', 'Laptop', 'Server', 'Printer'];
        foreach ($types as $name) {
            HardwareType::firstOrCreate(['name' => $name]);
        }
    }
}
