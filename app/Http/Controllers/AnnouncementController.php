<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Retrieve a paginated list of published announcements.
     */
    public function index(Request $request)
    {
        $query = Announcement::query()
            ->where('publish_at', '<=', now())
            ->orderBy('publish_at', 'desc');

        $announcements = $query->paginate(15); // Paginate the results

        return response()->json([
            'success' => true,
            'message' => 'Announcements retrieved successfully.',
            'data' => $announcements
        ]);
    }
}
