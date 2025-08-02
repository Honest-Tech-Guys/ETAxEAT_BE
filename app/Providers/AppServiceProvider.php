<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use TCG\Voyager\Facades\Voyager;
use App\Voyager\FormFields\OperatingHoursFormField;
use App\Voyager\FormFields\MenuEditorFormField;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Voyager::addFormField(OperatingHoursFormField::class);
        Voyager::addFormField(MenuEditorFormField::class);
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}