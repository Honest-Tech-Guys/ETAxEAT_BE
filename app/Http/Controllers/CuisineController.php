<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Cuisine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class CuisineController extends Controller
{
    public function groupedByCategory()
    {
        $categories = Category::withCount('cuisines')
            ->with(['cuisines'])
            ->has('cuisines')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Cuisines grouped by category retrieved successfully.',
            'data' => $categories
        ]);
    }

    public function filter(Request $request)
    {
        $query = Cuisine::withTranslation()->where('is_active', true);

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

                $query->select('cuisines.*')
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

        // Apply cuisine_type filter for multiple selections
        if ($request->has('cuisine_type') && !empty($request->input('cuisine_type'))) {
            $cuisineTypes = is_array($request->input('cuisine_type'))
                ? $request->input('cuisine_type')
                : json_decode(str_replace("'", '"', $request->input('cuisine_type')), true);
            
            if ($cuisineTypes) {
                $query->whereHas('cuisineTypes', function ($q) use ($cuisineTypes) {
                    $q->whereIn('slug', $cuisineTypes);
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

        // Get cuisines first, then filter by operating hours in PHP
        $cuisines = $query->with([
            'categories' => function ($q) { $q->withTranslation(); },
            'dishes' => function ($q) { $q->withTranslation(); },
            'cuisineTypes' => function ($q) { $q->withTranslation(); },
            'dishCategories' => function ($q) { $q->withTranslation(); }
        ])->get();

        $now = now();
        $currentDay = strtolower($now->format('l')); // e.g., 'monday'
        $currentTime = $now->setTimezone(config('app.timezone'))->format('H:i');

        $cuisines->each(function ($cuisine) use ($currentDay, $currentTime) {
            $isOpen = false;
            if ($cuisine->operating_hours) {
                $operatingHours = is_string($cuisine->operating_hours)
                    ? json_decode($cuisine->operating_hours, true)
                    : $cuisine->operating_hours;

                if (isset($operatingHours[$currentDay]) && !empty($operatingHours[$currentDay])) {
                    foreach ($operatingHours[$currentDay] as $hours) {
                        if (isset($hours['open']) && isset($hours['close'])) {
                            if ($hours['open'] <= $currentTime && $hours['close'] > $currentTime) {
                                $isOpen = true;
                                break; // Exit the loop as soon as we find an open slot
                            }
                        }
                    }
                }
            }
            $cuisine->status = $isOpen ? 'open' : 'closed';
        });


        // Filter by currently open cuisines using PHP
        if ($request->input('only_open')) {
            $cuisines = $cuisines->filter(function ($cuisine) {
                return $cuisine->status === 'open';
            })->values(); // Re-index the collection
        }

        return response()->json([
            'success' => true,
            'message' => 'Cuisines filtered successfully.',
            'data' => $cuisines
        ]);
    }
    /**
     * Get the 5 nearest cuisines based on latitude and longitude.
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

        $cuisines = Cuisine::query()
            ->select('cuisines.*')
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
            'message' => 'Nearest 5 cuisines retrieved successfully.',
            'data' => $cuisines
        ]);
    }

}
