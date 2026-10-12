<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    private const COLOR_KEY = 'color_scheme';

    // Official NAAP colors: Deep Blue, Golden Yellow, White
    private const DEFAULT_COLORS = [
        'primary' => '#002F6C',
        'accent'  => '#FFCC00',
        'surface' => '#FFFFFF',
    ];

    /**
     * Public: the login page needs the theme before anyone is authenticated.
     */
    public function colorScheme()
    {
        return response()->json([
            'success' => true,
            'data' => array_merge(self::DEFAULT_COLORS, AppSetting::getValue(self::COLOR_KEY, [])),
            'defaults' => self::DEFAULT_COLORS,
        ]);
    }

    public function updateColorScheme(Request $request)
    {
        if ($request->user()->role->name !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $hex = ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'];
        $validated = $request->validate([
            'primary' => $hex,
            'accent'  => $hex,
            'surface' => $hex,
        ]);

        $old = AppSetting::getValue(self::COLOR_KEY, self::DEFAULT_COLORS);
        $colors = array_map('strtoupper', $validated);
        AppSetting::setValue(self::COLOR_KEY, $colors);

        ActivityLog::log('updated', 'Updated system color scheme', null, $old, $colors);

        return response()->json([
            'success' => true,
            'message' => 'Color scheme updated successfully',
            'data' => $colors,
        ]);
    }

    public function resetColorScheme(Request $request)
    {
        if ($request->user()->role->name !== 'Admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $old = AppSetting::getValue(self::COLOR_KEY, self::DEFAULT_COLORS);
        AppSetting::setValue(self::COLOR_KEY, self::DEFAULT_COLORS);

        ActivityLog::log('updated', 'Reset system color scheme to NAAP defaults', null, $old, self::DEFAULT_COLORS);

        return response()->json([
            'success' => true,
            'message' => 'Color scheme reset to NAAP defaults',
            'data' => self::DEFAULT_COLORS,
        ]);
    }
}
