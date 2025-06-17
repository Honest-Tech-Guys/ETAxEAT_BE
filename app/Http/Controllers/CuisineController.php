<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Cuisine;
use Illuminate\Http\Request;

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

}