<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use TCG\Voyager\Models\Setting;

class SettingController extends Controller
{
    /**
     * Retrieve all Voyager settings.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $settings = Setting::all();
        $formattedSettings = [];

        foreach ($settings as $setting) {
            Arr::set($formattedSettings, $setting->key, $setting->value);
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings retrieved successfully.',
            'data' => $formattedSettings
        ]);
    }
}
