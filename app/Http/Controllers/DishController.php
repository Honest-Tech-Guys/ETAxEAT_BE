<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use Illuminate\Http\Request;

class DishController extends Controller
{
    /**
     * Get a list of dishes with a count of the cuisines they are in.
     * Can be filtered by a category to show only relevant dishes.
     * Dishes that are not in any cuisine are not included.
     */
    public function index(Request $request)
    {
        // Start the query, ensuring the dish is in at least one cuisine
        $query = Dish::has('cuisines')->withCount('cuisines');

        // If a category is selected, filter dishes to only include those
        // that are part of a cuisine within that category.
        if ($request->has('category')) {
            $categorySlug = $request->category;
            $query->whereHas('cuisines.categories', function ($q) use ($categorySlug) {
                $q->where('name', $categorySlug);
            });
        }

        $dishes = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Dishes retrieved successfully.',
            'data' => $dishes
        ]);
    }
}
