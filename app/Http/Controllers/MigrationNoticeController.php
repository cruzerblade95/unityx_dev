<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MigrationNoticeController extends Controller
{
    /**
     * Show the migration notice page
     */
    public function index()
    {
        return view('migration_notice');
    }

    /**
     * Check if the current request is from a unityx.dev domain
     */
    public static function shouldShowMigrationNotice(Request $request)
    {
        $host = $request->getHost();

        // Check if the host contains unityx.dev
        if (str_contains(strtolower($host), 'unityx.dev')) {
            return true;
        }

        return false;
    }
}
