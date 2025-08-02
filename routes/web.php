<?php

use App\Http\Controllers\MenuImageUploadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/admin');
});

Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
    Route::post('/upload-menu-image', [MenuImageUploadController::class, 'uploadImage'])
        ->name('admin.menu.upload-image');

    Route::delete('/delete-menu-image', [MenuImageUploadController::class, 'deleteImage'])
        ->name('admin.menu.delete-image');
});
