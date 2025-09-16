<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function filter(Request $request)
    {
        $query = Event::withTranslation()->where('is_active', true);

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

                $query->select('events.*')
                    ->selectRaw(
                        '(? * ACOS(COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(latitude)))) AS distance',
                        [$radius, $latitude, $longitude, $latitude]
                    )
                    ->whereNotNull(['latitude', 'longitude'])
                    ->having('distance', '<=', $maxDistance)
                    ->orderBy('distance', 'asc');
            }
        }

        // Apply event_category filter
        if ($request->has('event_category') && !empty($request->input('event_category'))) {
            $eventCategories = is_array($request->input('event_category'))
                ? $request->input('event_category')
                : json_decode(str_replace("'", '"', $request->input('event_category')), true);

            if ($eventCategories) {
                $query->whereHas('eventCategories', function ($q) use ($eventCategories) {
                    $q->whereIn('slug', $eventCategories);
                });
            }
        }

        // Apply event_type filter
        if ($request->has('event_type') && !empty($request->input('event_type'))) {
            $eventTypes = is_array($request->input('event_type'))
                ? $request->input('event_type')
                : json_decode(str_replace("'", '"', $request->input('event_type')), true);

            if ($eventTypes) {
                $query->whereHas('eventTypes', function ($q) use ($eventTypes) {
                    $q->whereIn('slug', $eventTypes);
                });
            }
        }

        $events = $query->with([
            'eventCategories' => function ($q) {
                $q->withTranslation();
            },
            'eventTypes' => function ($q) {
                $q->withTranslation();
            },
        ])->get();

        $now = now();
        $currentDay = strtolower($now->format('l')); // e.g., 'monday'
        $currentTime = $now->setTimezone(config('app.timezone'))->format('H:i');

        $events->each(function ($event) use ($currentDay, $currentTime) {
            $isOpen = false;
            if ($event->operating_hours) {
                $operatingHours = is_string($event->operating_hours)
                    ? json_decode($event->operating_hours, true)
                    : $event->operating_hours;

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
            $event->status = $isOpen ? 'open' : 'closed';
        });


        if ($request->input('only_open')) {
            $events = $events->filter(function ($event) {
                return $event->status === 'open';
            })->values(); // Re-index the collection
        }

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
