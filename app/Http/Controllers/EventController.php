<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function filter(Request $request)
    {
        $query = Event::query();

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

                $query->select('events.*')
                    ->selectRaw(
                        '(? * ACOS(COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(latitude)))) AS distance',
                        [$radius, $latitude, $longitude, $latitude]
                    )
                    ->whereNotNull(['latitude', 'longitude'])
                    ->having('distance', '<=', $distance)
                    ->orderBy('distance', 'asc');
            }
        }

        if ($request->has(['start_date', 'end_date'])) {
            $validator = Validator::make($request->all(), [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date'
            ]);

            if (!$validator->fails()) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date]);
            }
        }

        // Existing filters
        if ($request->has('event_category')) {
            $query->whereHas('eventCategories', function ($q) use ($request) {
                $q->where('slug', $request->event_category);
            });
        }

        if ($request->has('event_type')) {
            $query->whereHas('eventTypes', function ($q) use ($request) {
                $q->where('slug', $request->event_type);
            });
        }

        $events = $query->with(['eventCategories', 'eventTypes'])->get();

        return response()->json([
            'success' => true,
            'message' => 'Events filtered successfully.',
            'data' => $events
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

        $events = Event::query()
            ->select('events.*')
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
            'message' => 'Nearest 5 events retrieved successfully.',
            'data' => $events
        ]);
    }
}
