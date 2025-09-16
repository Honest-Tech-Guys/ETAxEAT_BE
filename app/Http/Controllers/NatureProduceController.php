<?php

namespace App\Http\Controllers;

use App\Models\NatureProduce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NatureProduceController extends Controller
{
    public function filter(Request $request)
    {
        $query = NatureProduce::withTranslation()->where('is_active', true);

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
        
        // Apply producer_type filter
        if ($request->has('producer_type') && !empty($request->input('producer_type'))) {
            $producerTypes = is_array($request->input('producer_type'))
                ? $request->input('producer_type')
                : json_decode(str_replace("'", '"', $request->input('producer_type')), true);

            if ($producerTypes) {
                $query->whereHas('producerTypes', function ($q) use ($producerTypes) {
                    $q->whereIn('slug', $producerTypes);
                });
            }
        }

        // Apply product_type filter
        if ($request->has('product_type') && !empty($request->input('product_type'))) {
            $productTypes = is_array($request->input('product_type'))
                ? $request->input('product_type')
                : json_decode(str_replace("'", '"', $request->input('product_type')), true);

            if ($productTypes) {
                $query->whereHas('productTypes', function ($q) use ($productTypes) {
                    $q->whereIn('slug', $productTypes);
                });
            }
        }


        $natureProduces = $query->with([
            'producerTypes' => function ($q) {
                $q->withTranslation();
            },
            'productTypes' => function ($q) {
                $q->withTranslation();
            },
        ])->get();

        $now = now();
        $currentDay = strtolower($now->format('l')); // e.g., 'monday'
        $currentTime = $now->setTimezone(config('app.timezone'))->format('H:i');

        $natureProduces->each(function ($natureProduce) use ($currentDay, $currentTime) {
            $isOpen = false;
            if ($natureProduce->operating_hours) {
                $operatingHours = is_string($natureProduce->operating_hours)
                    ? json_decode($natureProduce->operating_hours, true)
                    : $natureProduce->operating_hours;

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
            $natureProduce->status = $isOpen ? 'open' : 'closed';
        });

        if ($request->input('only_open')) {
            $natureProduces = $natureProduces->filter(function ($natureProduce) {
                return $natureProduce->status === 'open';
            })->values(); // Re-index the collection
        }

        return response()->json([
            'success' => true,
            'message' => 'Nature produces filtered successfully.',
            'data' => $natureProduces
        ]);
    }
}
