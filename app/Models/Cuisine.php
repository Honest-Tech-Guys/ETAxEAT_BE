<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;

class Cuisine extends Model
{
    use Translatable;

    protected $translatable = ['name', 'about', 'description'];
    protected $guarded = [];

    protected $fillable = ['name', 'about', 'description', 'operating_hours', 'features', 'images', 'latitude', 'longitude', 'rating', 'is_active'];
    protected $casts = [
        'operating_hours' => 'array',
        'features' => 'array',
        'images' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'rating' => 'double',
    ];

     /**
     * Accessor for the operating_hours attribute.
     *
     * This method automatically formats the operating hours array into a
     * readable HTML string for display in Voyager's Browse and Read views.
     *
     * @param  string|null  $value The raw JSON string from the database.
     * @return string
     */
    // public function getOperatingHoursAttribute($value)
    // {
    //     // **THE FIX:** Manually decode the JSON string into an array.
    //     // We use true to get an associative array.
    //     $hoursArray = json_decode($value, true);

    //     // Now, this check will work correctly. We also check for JSON errors.
    //     if (json_last_error() !== JSON_ERROR_NONE || empty($hoursArray) || !is_array($hoursArray)) {
    //         return 'Not Set';
    //     }

    //     $display = [];
    //     $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    //     // Loop through days in order to ensure consistent display
    //     foreach ($daysOfWeek as $day) {
    //         // Check if the key exists and the array for that day is not empty
    //         if (isset($hoursArray[$day]) && !empty($hoursArray[$day])) {
    //             $daySlots = [];
    //             foreach ($hoursArray[$day] as $slot) {
    //                 // Ensure keys exist to prevent errors
    //                 $open = $slot['open'] ?? 'N/A';
    //                 $close = $slot['close'] ?? 'N/A';
    //                 // Format times for readability (e.g., 9:00 AM - 5:00 PM)
    //                 $daySlots[] = date('g:i a', strtotime($open)) . ' - ' . date('g:i a', strtotime($close));
    //             }
    //             $display[] = '<strong>' . ucfirst($day) . ':</strong> ' . implode(', ', $daySlots);
    //         }
    //     }

    //     if (empty($display)) {
    //         return 'Closed';
    //     }

    //     // Use new Raw HTML helper to prevent Blade from escaping the HTML tags
    //     return new \Illuminate\Support\HtmlString(implode('<br>', $display));
    // }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_cuisine');
    }

    public function dishes()
    {
        return $this->belongsToMany(Dish::class, 'cuisine_dish');
    }
    public function cuisineTypes()
    {
        return $this->belongsToMany(CuisineType::class, 'cuisine_type_cuisine');
    }
    public function dishCategories()
    {
        return $this->belongsToMany(DishCategory::class, 'cuisine_dish_category');
    }
}
