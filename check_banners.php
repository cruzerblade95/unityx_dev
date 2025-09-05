<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$banners = App\Models\BannerAdvertisement::all();

echo "Current Banner States:\n";
echo "=====================\n";

foreach ($banners as $banner) {
    echo "ID: {$banner->id}\n";
    echo "Title: {$banner->title}\n";
    echo "show_title: " . ($banner->show_title ? 'true' : 'false') . "\n";
    echo "show_description: " . ($banner->show_description ? 'true' : 'false') . "\n";
    echo "---\n";
}
