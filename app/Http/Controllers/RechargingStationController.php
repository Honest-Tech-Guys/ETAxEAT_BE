<?php

namespace App\Http\Controllers;

use App\Models\RechargingStation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RechargingStationController extends Controller
{
    public function filter(Request $request)
    {
        $query = RechargingStation::query();

        if ($request->has('recharging_category')) {
            $query->whereHas('rechargingCategories', function ($q) use ($request) {
                $q->where('slug', $request->recharging_category);
            });
        }

        if ($request->has('charging_type')) {
            $query->whereHas('chargingTypes', function ($q) use ($request) {
                $q->where('slug', $request->charging_type);
            });
        }

        if ($request->has('vehicle_type')) {
            $query->whereHas('vehicleTypes', function ($q) use ($request) {
                $q->where('slug', $request->vehicle_type);
            });
        }

        if ($request->has('charging_power')) {
            $query->whereHas('chargingPowers', function ($q) use ($request) {
                $q->where('slug', 'like', '%' . $request->charging_power . '%');
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
