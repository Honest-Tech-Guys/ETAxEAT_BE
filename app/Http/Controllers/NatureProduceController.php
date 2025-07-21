<?php

namespace App\Http\Controllers;

use App\Models\NatureProduce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NatureProduceController extends Controller
{
    /**
     * Filter Nature Produce locations by producer type, product type, and/or distance.
     */
    public function filter(Request $request)
    {
        $query = NatureProduce::query();

        // Add distance filtering if location data is provided
        if ($request->hasAll(['latitude', 'longitude', 'distance'])) {
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'distance' => 'required|numeric|min:0',
            ]);

            if (!$validator->fails()) {
                $latitude = $request->latitude;
                $longitude = $request->longitude;
                $maxDistance = $request->distance; // in kilometers
                $radius = 6371; // Earth's radius

                $query->select('nature_produces.*')
                    ->selectRaw(
                        '(? * ACOS(COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(latitude)))) AS distance',
                        [$radius, $latitude, $longitude, $latitude]
                    )
                    ->whereNotNull(['latitude', 'longitude'])
                    ->having('distance', '<=', $maxDistance)
                    ->orderBy('distance', 'asc');
            }
        }

        // Filter by Producer Type
        if ($request->has('producer_type')) {
            $query->whereHas('producerTypes', function ($q) use ($request) {
                $q->where('slug', $request->producer_type);
            });
        }

        // Filter by Product Type
        if ($request->has('product_type')) {
            $query->whereHas('productTypes', function ($q) use ($request) {
                $q->where('slug', $request->product_type);
            });
        }

        $results = $query->with(['producerTypes', 'productTypes'])->get();

        return response()->json([
            'success' => true,
            'message' => 'Nature Produce locations filtered successfully.',
            'data' => $results
        ]);
    }

    /**
     * Get the 5 nearest Nature Produce locations.
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
        $radius = 6371;

        $results = NatureProduce::query()
            ->select('nature_produces.*')
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
            'message' => 'Nearest 5 Nature Produce locations retrieved successfully.',
            'data' => $results
        ]);
    }
}
