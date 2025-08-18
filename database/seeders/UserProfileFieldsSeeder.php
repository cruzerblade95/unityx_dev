<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserProfileFieldsSeeder extends Seeder
{
    public function run(): void
    {
        // Example: update all users with demo data for new fields
        User::query()->update([
            'address' => '123 Main St',
            'state' => 'California',
            'zipCode' => '90001',
            'country' => 'Malaysia',
            'phone' => '123-456-7890',
            'organization' => 'Demo Org',
            'language' => 'en',
            'timezone' => 'America/Los_Angeles',
            'currency' => 'USD',
        ]);
    }
}
