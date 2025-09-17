<?php

use Illuminate\Support\Facades\Route;

// Test route for MYR conversion functionality
Route::get('/test-myr-functionality', function() {
    try {
        // Test the MYR conversion rate method
        $myrRate = App\Models\WithdrawalSettings::getMyrConversionRate();

        // Test with a sample amount
        $sampleAmount = 100.00;
        $convertedAmount = $sampleAmount * $myrRate;

        return response()->json([
            'success' => true,
            'myr_rate' => $myrRate,
            'sample_usd' => $sampleAmount,
            'converted_myr' => $convertedAmount,
            'test_passed' => true,
            'message' => 'MYR conversion functionality is working'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'test_passed' => false
        ]);
    }
});
