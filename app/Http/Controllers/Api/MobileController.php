<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MobileController extends Controller
{
    /**
     * Check app version for OTA updates.
     */
    public function version(Request $request)
    {
        $platform = $request->get('platform');
        $currentVersion = $request->get('current_version');
        $buildNumber = $request->get('build_number');

        // Logic to check version would go here.
        // For this MVP, we return a hardcoded response simulating an update.
        
        return response()->json([
            'latest_version' => [
                'version' => '1.1.0',
                'build_number' => 2,
                'download_url' => 'https://play.google.com/store/apps/details?id=com.liosync.app', // Placeholder
                'is_force_update' => false,
                'release_notes' => 'New features and bug fixes',
                'released_at' => now()->toIso8601String(),
            ]
        ]);
    }

    /**
     * Register push notification token.
     */
    public function pushToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'platform' => 'required|in:android,ios',
        ]);

        $user = $request->user();

        // Save token logic (e.g. to a user_devices table)
        // For now, we'll just log it or assume it's saved.
        // You might want to create a UserDevice model and table.
        // Log::info('Push token registered', ['user_id' => $user->id, 'token' => $request->token]);

        return response()->json([
            'success' => true,
            'message' => 'Token registered successfully'
        ]);
    }
}
