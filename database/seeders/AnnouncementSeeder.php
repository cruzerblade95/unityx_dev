<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Announcement;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Announcement::create([
            'title' => 'Welcome to Unity X Platform!',
            'content' => 'Deposit, withdrawal & p2p trading is available now! Next update will include the Referral System Bonus. Stay tuned for more exciting features!',
            'type' => 'primary',
            'is_active' => true,
            'is_dismissible' => true,
            'sort_order' => 0
        ]);

        Announcement::create([
            'title' => 'Platform Maintenance Notice',
            'content' => 'We will be performing scheduled maintenance on <strong>Sunday, 2:00 AM - 4:00 AM UTC</strong>. Please plan your activities accordingly.',
            'type' => 'warning',
            'is_active' => false, // Not active by default
            'is_dismissible' => true,
            'sort_order' => 1
        ]);
    }
}
