<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Retrieve a paginated list of published announcements.
     */
    public function index()
    {
        $query = Announcement::withTranslation()->where('is_active', true)
            ->where('publish_at', '<=', now())
            ->orderBy('publish_at', 'desc');

        $announcements = $query->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Announcements retrieved successfully.',
            'data' => $announcements
        ]);
    }
}
