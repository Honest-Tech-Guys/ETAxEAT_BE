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

        if ($request->has(['latitude', 'longitude', 'distance'])) {
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'distance' => 'required|numeric|min:0',
            ]);

            if (!$validator->fails()) {
                $latitude = $request->latitude;
                $longitude = $request->longitude;
                $maxDistance = $request->distance;
                $radius = 6371; // Earth's radius in kilometers

                $query->select('recharging_stations.*')
                    ->selectRaw(
                        '(? * ACOS(COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(latitude)))) AS distance',
                        [$radius, $latitude, $longitude, $latitude]
                    )
                    ->whereNotNull(['latitude', 'longitude'])
                    ->having('distance', '<=', $maxDistance)
                    ->orderBy('distance', 'asc');
            }
        }

        // Apply recharging_category filter
        if ($request->has('recharging_category') && !empty($request->input('recharging_category'))) {
            $rechargingCategories = is_array($request->input('recharging_category'))
                ? $request->input('recharging_category')
                : json_decode(str_replace("'", '"', $request->input('recharging_category')), true);

            if ($rechargingCategories) {
                $query->whereHas('rechargingCategories', function ($q) use ($rechargingCategories) {
                    $q->whereIn('slug', $rechargingCategories);
                });
            }
        }

        // Apply charging_type filter
        if ($request->has('charging_type') && !empty($request->input('charging_type'))) {
            $chargingTypes = is_array($request->input('charging_type'))
                ? $request->input('charging_type')
                : json_decode(str_replace("'", '"', $request->input('charging_type')), true);

            if ($chargingTypes) {
                $query->whereHas('chargingTypes', function ($q) use ($chargingTypes) {
                    $q->whereIn('slug', $chargingTypes);
                });
            }
        }

        // Apply vehicle_type filter
        if ($request->has('vehicle_type') && !empty($request->input('vehicle_type'))) {
            $vehicleTypes = is_array($request->input('vehicle_type'))
                ? $request->input('vehicle_type')
                : json_decode(str_replace("'", '"', $request->input('vehicle_type')), true);

            if ($vehicleTypes) {
                $query->whereHas('vehicleTypes', function ($q) use ($vehicleTypes) {
                    $q->whereIn('slug', $vehicleTypes);
                });
            }
        }

        // Apply charging_power filter
        if ($request->has('charging_power') && !empty($request->input('charging_power'))) {
            $chargingPowers = is_array($request->input('charging_power'))
                ? $request->input('charging_power')
                : json_decode(str_replace("'", '"', $request->input('charging_power')), true);

            if ($chargingPowers) {
                $query->whereHas('chargingPowers', function ($q) use ($chargingPowers) {
                    $q->whereIn('slug', $chargingPowers);
                });
            }
        }


        $rechargingStations = $query->with([
            'rechargingCategories' => function ($q) {
                $q->withTranslation();
            },
            'chargingTypes' => function ($q) {
                $q->withTranslation();
            },
            'vehicleTypes' => function ($q) {
                $q->withTranslation();
            },
            'chargingPowers' => function ($q) {
                $q->withTranslation();
            },
        ])->get();

        $now = now();
        $currentDay = strtolower($now->format('l')); // e.g., 'monday'
        $currentTime = $now->setTimezone(config('app.timezone'))->format('H:i');

        $rechargingStations->each(function ($station) use ($currentDay, $currentTime) {
            $isOpen = false;
            if ($station->operating_hours) {
                $operatingHours = is_string($station->operating_hours)
                    ? json_decode($station->operating_hours, true)
                    : $station->operating_hours;

                if (isset($operatingHours[$currentDay]) && !empty($operatingHours[$currentDay])) {
                    foreach ($operatingHours[$currentDay] as $hours) {
                        if (isset($hours['open']) && isset($hours['close'])) {
                            if ($hours['open'] <= $currentTime && $hours['close'] > $currentTime) {
                                $isOpen = true;
                                break; // Exit loop once an open slot is found
                            }
                        }
                    }
                }
            }
            $station->status = $isOpen ? 'open' : 'closed';
        });

        if ($request->input('only_open')) {
            $rechargingStations = $rechargingStations->filter(function ($station) {
                return $station->status === 'open';
            })->values(); // Re-index the collection
        }

        return response()->json([
            'success' => true,
            'message' => 'Recharging stations filtered successfully.',
            'data' => $rechargingStations
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
