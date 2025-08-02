<?php

namespace App\Voyager\FormFields;

use TCG\Voyager\FormFields\AbstractHandler;

class MenuEditorFormField extends AbstractHandler
{
    // This is the name that will appear in the BREAD editor dropdown
    protected $codename = 'menu_editor';

    // This function tells Voyager which view file to use for this form field
    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('vendor.voyager.formfields.menu_editor', [
            'row'             => $row,
            'options'         => $options,
            'dataType'        => $dataType,
            'dataTypeContent' => $dataTypeContent,
        ]);
    }
}
