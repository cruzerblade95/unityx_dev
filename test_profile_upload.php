<?php
// Simple test script to check User model and profile_photo_path field

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

use App\Models\User;

try {
    echo "Testing User model and profile_photo_path field...\n";

    $user = User::first();
    if ($user) {
        echo "User found: " . $user->email . "\n";
        echo "Current profile_photo_path: " . ($user->profile_photo_path ?? 'null') . "\n";
        echo "Fillable fields: " . implode(', ', $user->getFillable()) . "\n";

        // Test if we can set profile_photo_path
        $originalPath = $user->profile_photo_path;
        $user->profile_photo_path = 'test/path.jpg';
        $user->save();
        echo "Test save successful\n";

        // Restore original
        $user->profile_photo_path = $originalPath;
        $user->save();
        echo "Restored original path\n";
    } else {
        echo "No users found\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
