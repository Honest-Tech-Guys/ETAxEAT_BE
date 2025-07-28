<?php

namespace App\Http\Controllers\Voyager;

use Illuminate\Http\Request;
use TCG\Voyager\Http\Controllers\VoyagerBaseController;

class OperationHoursBreadController extends VoyagerBaseController
{
    // This function will run INSTEAD of the default Voyager update method.
    public function update(Request $request, $id)
    {
        // Get the operating hours data submitted from the form
        $operatingHoursInput = $request->input('operating_hours');

        // Check if data was submitted for this field
        if ($operatingHoursInput && isset($operatingHoursInput['day'])) {
            
            // Build the correct JSON structure.
            $structuredHours = [
                'monday'    => [], 'tuesday'   => [], 'wednesday' => [],
                'thursday'  => [], 'friday'    => [], 'saturday'  => [], 'sunday'    => [],
            ];

            for ($i = 0; $i < count($operatingHoursInput['day']); $i++) {
                $day = $operatingHoursInput['day'][$i];
                $open = $operatingHoursInput['open'][$i];
                $close = $operatingHoursInput['close'][$i];

                if ($day && $open && $close) {
                    $structuredHours[$day][] = ['open' => $open, 'close' => $close];
                }
            }

            // CORRECTED: Merge the raw PHP array and let Laravel handle the encoding.
            $request->merge(['operating_hours' => $structuredHours]);
        }

        // Now, we call the ORIGINAL Voyager update method.
        // It will now save the corrected data without any issues.
        return parent::update($request, $id);
    }
}