<?php

namespace App\Http\Controllers;

use App\Models\RechargingStation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RechargingStationController extends Controller
{
    public function filter(Request $request)
    {
        $query = RechargingStation::withTranslation()->where('is_active', true);

        // Add distance filtering if location data is provided
        if ($request->has(['latitude', 'longitude', 'distance'])) {
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'distance' => 'required|numeric|min:0',
            ]);

            if (!$validator->fails()) {
                $latitude = $request->latitude;
                $longitude = $request->longitude;
                $distance = $request->distance;
                $radius = 6371;

                $query->select('recharging_stations.*')
                    ->selectRaw(
                        '(? * ACOS(COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(latitude)))) AS distance',
                        [$radius, $latitude, $longitude, $latitude]
                    )
                    ->whereNotNull(['latitude', 'longitude'])
                    ->having('distance', '<=', $distance)
                    ->orderBy('distance', 'asc');
            }
        }

        // Existing filters
        if ($request->has('recharging_category') && !empty($request->input('recharging_category'))) {
            $json = str_replace("'", '"', $request->input('recharging_category'));
            $query->whereHas('rechargingCategories', function ($q) use ($json) {
            $q->whereIn('slug', json_decode($json, true));
            });
        }
        if ($request->has('charging_type') && !empty($request->input('charging_type'))) {
            $json = str_replace("'", '"', $request->input('charging_type'));
            $query->whereHas('chargingTypes', function ($q) use ($json) {
            $q->whereIn('slug', json_decode($json, true));
            });
        }
        if ($request->has('vehicle_type') && !empty($request->input('vehicle_type'))) {
            $json = str_replace("'", '"', $request->input('vehicle_type'));
            $query->whereHas('vehicleTypes', function ($q) use ($json) {
            $q->whereIn('slug', json_decode($json, true));
            });
        }
        if ($request->has('charging_power') && !empty($request->input('charging_power'))) {
            $json = str_replace("'", '"', $request->input('charging_power'));
            $query->whereHas('chargingPowers', function ($q) use ($json) {
            $q->whereIn('slug', json_decode($json, true));
            });
        }

        $stations = $query->with(['rechargingCategories', 'chargingTypes', 'vehicleTypes', 'chargingPowers'])->get();

        return response()->json([
            'success' => true,
            'message' => 'Recharging stations filtered successfully.',
            'data' => $stations
        ]);
    }

    public function getNearest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid latitude or longitude provided.',
                'errors' => $validator->errors()
            ], 400);
        }

        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $radius = 6371; // Earth's radius in kilometers

        $stations = RechargingStation::query()
            ->select('recharging_stations.*')
            ->selectRaw(
                '(? * ACOS(COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(latitude)))) AS distance',
                [$radius, $latitude, $longitude, $latitude]
            )
            ->whereNotNull(['latitude', 'longitude'])
            ->orderBy('distance', 'asc')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Nearest 5 recharging stations retrieved successfully.',
            'data' => $stations
        ]);
    }
}
