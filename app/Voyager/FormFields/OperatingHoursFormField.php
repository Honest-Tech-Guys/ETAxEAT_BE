<?php

namespace App\Voyager\FormFields;

use TCG\Voyager\FormFields\AbstractHandler;

class OperatingHoursFormField extends AbstractHandler
{
    protected $codename = 'operating_hours';

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('vendor.voyager.formfields.operating_hours', [
            'row'             => $row,
            'options'         => $options,
            'dataType'        => $dataType,
            'dataTypeContent' => $dataTypeContent,
        ]);
    }

    public function getContentBasedOnRequest($request, $slug, $row, $options)
    {
        $fieldData = $request->input($row->field);

        if (is_null($fieldData) || !isset($fieldData['day'])) {
            return '[]';
        }

        $structuredHours = [
            'monday'    => [],
            'tuesday'   => [],
            'wednesday' => [],
            'thursday'  => [],
            'friday'    => [],
            'saturday'  => [],
            'sunday'    => [],
        ];

        for ($i = 0; $i < count($fieldData['day']); $i++) {
            $day = $fieldData['day'][$i];
            $open = $fieldData['open'][$i];
            $close = $fieldData['close'][$i];

            if ($day && $open && $close) {
                $structuredHours[$day][] = [
                    'open' => $open,
                    'close' => $close,
                ];
            }
        }

        return json_encode($structuredHours);
    }
}