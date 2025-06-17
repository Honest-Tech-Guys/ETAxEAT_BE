<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get a list of categories with a count of their cuisines.
     * Can be filtered by a dish to show only relevant categories.
     * Categories with no cuisines are not included.
     */
    public function index(Request $request)
    {
        // Start the query, ensuring the category has at least one cuisine
        $query = Category::has('cuisines')->withCount('cuisines');

        // If a dish is selected, filter categories to only include
        // those that have at least one cuisine offering that dish.
        if ($request->has('dish')) {
            $dishName = $request->dish;
            $query->whereHas('cuisines.dishes', function ($q) use ($dishName) {
                $q->where('name', 'like', '%' . $dishName . '%');
            });
        }

        $categories = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully.',
            'data' => $categories
        ]);
    }
}
