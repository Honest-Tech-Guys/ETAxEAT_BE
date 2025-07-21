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
        // Start the query with the withTranslation() scope
        $query = Cuisine::withTranslation();

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
                $radius = 6371;

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

        // Apply other existing filters
        if ($request->has('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }
        if ($request->has('dish')) {
            $query->whereHas('dishes', function ($q) use ($request) {
                $q->where('slug', $request->dish); // Assuming you added slug to dishes
            });
        }

        // Eager load relationships and their translations
        $cuisines = $query->with([
            'categories' => function ($q) {
                $q->withTranslation();
            },
            'dishes' => function ($q) {
                $q->withTranslation();
            }
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