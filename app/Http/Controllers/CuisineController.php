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
        $query = Cuisine::query();

        if ($request->has('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->has('dish')) {
            $query->whereHas('dishes', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->dish . '%');
            });
        }

        $cuisines = $query->with(['categories', 'dishes'])->get();

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