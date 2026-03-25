<?php

namespace Database\Seeders;

use App\Models\Staff;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Staff::query()->insert([
            [
                'name' => 'Ali Hassan',
                'phone' => '0501002000',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fatima Noor',
                'phone' => '0503004000',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
