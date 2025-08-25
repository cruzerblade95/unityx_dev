<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BannerAdvertisement;

class BannerAdvertisementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banners = [
            [
                'title' => '🚀 New AI Trading Features Available!',
                'description' => 'Experience enhanced trading algorithms with 15% higher success rates. Limited time offer!',
                'image_path' => null,
                'button_text' => 'Explore AI Trading',
                'link_url' => 'https://example.com/ai-trading',
                'background_color' => '#667eea',
                'gradient_color' => '#764ba2',
                'text_color' => '#ffffff',
                'sort_order' => 1,
                'is_active' => true,
                'start_date' => now(),
                'end_date' => now()->addDays(30),
            ],
            [
                'title' => '💰 USDT Marketplace Now Live!',
                'description' => 'Buy and sell USDT with competitive rates. Secure P2P transactions with escrow protection.',
                'image_path' => null,
                'button_text' => 'Visit Marketplace',
                'link_url' => 'https://example.com/marketplace',
                'background_color' => '#f093fb',
                'gradient_color' => '#f5576c',
                'text_color' => '#ffffff',
                'sort_order' => 2,
                'is_active' => true,
                'start_date' => now(),
                'end_date' => now()->addDays(45),
            ],
            [
                'title' => '🔔 Connect Your Telegram!',
                'description' => 'Get instant notifications for trades, deposits, and withdrawals directly on Telegram.',
                'image_path' => null,
                'button_text' => 'Connect Now',
                'link_url' => 'https://t.me/your_bot',
                'background_color' => '#4facfe',
                'gradient_color' => '#00f2fe',
                'text_color' => '#ffffff',
                'sort_order' => 3,
                'is_active' => true,
                'start_date' => now(),
                'end_date' => now()->addDays(60),
            ],
            [
                'title' => '🎯 Special Promotion: 20% Bonus!',
                'description' => 'Limited time offer! Get 20% bonus on your first deposit. Terms and conditions apply.',
                'image_path' => null,
                'button_text' => 'Claim Bonus',
                'link_url' => 'https://example.com/promotion',
                'background_color' => '#ff6b6b',
                'gradient_color' => '#ffa726',
                'text_color' => '#ffffff',
                'sort_order' => 4,
                'is_active' => true,
                'start_date' => now(),
                'end_date' => now()->addDays(15),
            ],
            [
                'title' => '📈 Advanced Trading Tools',
                'description' => 'Access professional trading tools and analytics. Take your trading to the next level.',
                'image_path' => null,
                'button_text' => 'Learn More',
                'link_url' => 'https://example.com/trading-tools',
                'background_color' => '#26a69a',
                'gradient_color' => '#42a5f5',
                'text_color' => '#ffffff',
                'sort_order' => 5,
                'is_active' => false, // This one is inactive for testing
                'start_date' => now(),
                'end_date' => now()->addDays(90),
            ],
        ];

        foreach ($banners as $banner) {
            BannerAdvertisement::create($banner);
        }
    }
}
