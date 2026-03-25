<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::query()->insert([
            [
                'name' => 'John Doe',
                'phone' => '0551234567',
                'address' => 'Dubai, UAE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sarah Khan',
                'phone' => '0559876543',
                'address' => 'Al Barsha, Dubai',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
