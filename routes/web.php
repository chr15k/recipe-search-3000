<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return response()->json(
        data: [
            'application' => 'Recipe Search 3000',
            'status' => 'Ready to serve your recipe needs',
            'version' => '0.0.1',
            'endpoints' => [
                '/api/recipes/search' => 'Search for recipes',
                '/api/recipes/{slug}' => 'Recipe details',
            ],
        ],
        options: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    );
});
