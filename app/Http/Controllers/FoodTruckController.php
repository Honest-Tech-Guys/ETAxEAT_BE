<?php

namespace App\Http\Controllers;

use App\Models\FoodTruck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FoodTruckController extends Controller
{
    /**
     * Filter food trucks by category and/or dish.
     */
    public function filter(Request $request)
    {
        $query = FoodTruck::withTranslation()->where('is_active', true);
        
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

                $query->select('food_trucks.*')
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
        if ($request->has('category') && !empty($request->input('category'))) {
            $json = str_replace("'", '"', $request->input('category'));
            $query->whereHas('categories', function ($q) use ($json) {
                $q->whereIn('slug', json_decode($json, true));
            });
        }
        if ($request->has('dish_category') && !empty($request->input('dish_category'))) {
            $json = str_replace("'", '"', $request->input('dish_category'));
            $query->whereHas('dishCategories', function ($q) use ($json) {
                $q->whereIn('slug', json_decode($json, true));
            });
        }
        if ($request->has('foodtruck_type') && !empty($request->input('foodtruck_type'))) {
            $json = str_replace("'", '"', $request->input('foodtruck_type'));
            $query->whereHas('foodTruckTypes', function ($q) use ($json) {
                $q->whereIn('slug', json_decode($json, true));
            });
        }

        if ($request->has('dish')) {
            $json = str_replace("'", '"', $request->input('dish'));
            $query->whereHas('dishes', function ($q) use ($json) {
                $q->whereIn('slug', json_decode($json, true));
            });
        }

        $foodTrucks = $query->with([
            'categories' => function ($q) {
                $q->withTranslation();
            },
            'dishes' => function ($q) {
                $q->withTranslation();
            },
            'foodTruckTypes' => function ($q) {
                $q->withTranslation();
            },
            'dishCategories' => function ($q) {
                $q->withTranslation();
            }
        ])->get();

        if ($request->input('only_open')) {
            $now = now();
            $currentDay = strtolower($now->format('l')); // e.g., 'monday'
            $currentTime = $now->setTimezone(config('app.timezone'))->format('H:i');

            $foodTrucks = $foodTrucks->filter(function ($foodTruck) use ($currentDay, $currentTime) {
                if (!$foodTruck->operating_hours) {
                    return false;
                }

                $operatingHours = is_string($foodTruck->operating_hours)
                    ? json_decode($foodTruck->operating_hours, true)
                    : $foodTruck->operating_hours;

                if (!isset($operatingHours[$currentDay]) || empty($operatingHours[$currentDay])) {
                    return false;
                }

                foreach ($operatingHours[$currentDay] as $hours) {
                    if (isset($hours['open']) && isset($hours['close'])) {
                        if ($hours['open'] <= $currentTime && $hours['close'] > $currentTime) {
                            return true;
                        }
                    }
                }

                return false;
            })->values();
        }

        return response()->json([
            'success' => true,
            'message' => 'Food trucks filtered successfully.',
            'data' => $foodTrucks
        ]);
    }

    /**
     * Get the 5 nearest food trucks based on latitude and longitude.
     */
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

        $foodTrucks = FoodTruck::query()
            ->select('food_trucks.*')
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
            'message' => 'Nearest 5 food trucks retrieved successfully.',
            'data' => $foodTrucks
        ]);
    }
}