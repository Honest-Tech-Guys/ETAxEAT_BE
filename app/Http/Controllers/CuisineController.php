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
        // Start the query with the withTranslation() scope and filter active cuisines
        $query = Cuisine::withTranslation()->where('is_active', true);
        
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
        
        // UPDATED: Apply category filter for multiple selections
        if ($request->has('category') && !empty($request->input('category'))) {
            $json = str_replace("'", '"', $request->input('category'));
            $query->whereHas('categories', function ($q) use ($json) {
                $q->whereIn('slug', json_decode($json, true));
            });
        }
        
        // UPDATED: Apply cuisine_type filter for multiple selections
        if ($request->has('cuisine_type') && !empty($request->input('cuisine_type'))) {
            $json = str_replace("'", '"', $request->input('cuisine_type'));
            $query->whereHas('cuisineTypes', function ($q) use ($json) {
                $q->whereIn('slug', json_decode($json, true));
            });
        }

        // UPDATED: Apply dish_category filter for multiple selections
        if ($request->has('dish_category') && !empty($request->input('dish_category'))) {
            $json = str_replace("'", '"', $request->input('dish_category'));
            $query->whereHas('dishCategories', function ($q) use ($json) {
                $q->whereIn('slug', json_decode($json, true));
            });
        }

        // UPDATED: Apply dish filter for multiple selections
        if ($request->has('dish') && !empty($request->input('dish'))) {
            $json = str_replace("'", '"', $request->input('dish'));
            $query->whereHas('dishes', function ($q) use ($json) {
                $q->whereIn('slug', json_decode($json, true));
            });
        }

        // Eager load relationships and their translations
        $cuisines = $query->with([
            'categories' => function ($q) { $q->withTranslation(); },
            'dishes' => function ($q) { $q->withTranslation(); },
            'cuisineTypes' => function ($q) { $q->withTranslation(); },
            'dishCategories' => function ($q) { $q->withTranslation(); }
        ])->get();

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