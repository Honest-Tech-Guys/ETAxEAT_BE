<?php

namespace App\Http\Controllers;

use App\Models\FoodTruck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FoodTruckController extends Controller
{
    public function filter(Request $request)
    {
        $query = FoodTruck::withTranslation()->where('is_active', true);

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

                $query->select('food_trucks.*')
                    ->selectRaw(
                        '(? * ACOS(COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(latitude)))) AS distance',
                        [$radius, $latitude, $longitude, $latitude]
                    )
                    ->whereNotNull(['latitude', 'longitude'])
                    ->having('distance', '<=', $maxDistance)
                    ->orderBy('distance', 'asc');
            }
        }

        // Apply category filter for multiple selections
        if ($request->has('category') && !empty($request->input('category'))) {
            $categories = is_array($request->input('category'))
                ? $request->input('category')
                : json_decode(str_replace("'", '"', $request->input('category')), true);

            if ($categories) {
                $query->whereHas('categories', function ($q) use ($categories) {
                    $q->whereIn('slug', $categories);
                });
            }
        }

        // Apply dish_category filter for multiple selections
        if ($request->has('dish_category') && !empty($request->input('dish_category'))) {
            $dishCategories = is_array($request->input('dish_category'))
                ? $request->input('dish_category')
                : json_decode(str_replace("'", '"', $request->input('dish_category')), true);

            if ($dishCategories) {
                $query->whereHas('dishCategories', function ($q) use ($dishCategories) {
                    $q->whereIn('slug', $dishCategories);
                });
            }
        }

        // Apply dish filter for multiple selections
        if ($request->has('dish') && !empty($request->input('dish'))) {
            $dishes = is_array($request->input('dish'))
                ? $request->input('dish')
                : json_decode(str_replace("'", '"', $request->input('dish')), true);

            if ($dishes) {
                $query->whereHas('dishes', function ($q) use ($dishes) {
                    $q->whereIn('slug', $dishes);
                });
            }
        }

        // Apply food_truck_type filter
        if ($request->has('food_truck_type') && !empty($request->input('food_truck_type'))) {
            $foodTruckTypes = is_array($request->input('food_truck_type'))
                ? $request->input('food_truck_type')
                : json_decode(str_replace("'", '"', $request->input('food_truck_type')), true);
        
            if ($foodTruckTypes) {
                $query->whereHas('foodTruckTypes', function ($q) use ($foodTruckTypes) {
                    $q->whereIn('slug', $foodTruckTypes);
                });
            }
        }


        $foodTrucks = $query->with([
            'categories' => function ($q) { $q->withTranslation(); },
            'dishes' => function ($q) { $q->withTranslation(); },
            'dishCategories' => function ($q) { $q->withTranslation(); },
            'foodTruckTypes' => function ($q) { $q->withTranslation(); },
        ])->get();

        $now = now();
        $currentDay = strtolower($now->format('l')); // e.g., 'monday'
        $currentTime = $now->setTimezone(config('app.timezone'))->format('H:i');
        
        $foodTrucks->each(function ($foodTruck) use ($currentDay, $currentTime) {
            $isOpen = false;
            if ($foodTruck->operating_hours) {
                $operatingHours = is_string($foodTruck->operating_hours)
                    ? json_decode($foodTruck->operating_hours, true)
                    : $foodTruck->operating_hours;

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
            $foodTruck->status = $isOpen ? 'open' : 'closed';
        });

        if ($request->input('only_open')) {
            $foodTrucks = $foodTrucks->filter(function ($foodTruck) {
                return $foodTruck->status === 'open';
            })->values(); // Re-index the collection
        }

        return response()->json([
            'success' => true,
            'message' => 'Food trucks filtered successfully.',
            'data' => $foodTrucks
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
