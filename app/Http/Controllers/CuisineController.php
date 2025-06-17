<?php

namespace App\Http\Controllers;

use App\Models\Category;
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

}
