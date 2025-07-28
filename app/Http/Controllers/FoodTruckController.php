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
        $query = FoodTruck::query();

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
        if ($request->has('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }
        if ($request->has('dish_category')) {
            $query->whereHas('dishCategories', function ($q) use ($request) {
                $q->where('slug', $request->dish_category);
            });
        }
        if ($request->has('foodtruck_type')) {
            $query->whereHas('foodTruckTypes', function ($q) use ($request) {
                $q->where('slug', $request->foodtruck_type);
            });
        }

        if ($request->has('dish')) {
            $query->whereHas('dishes', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->dish . '%');
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