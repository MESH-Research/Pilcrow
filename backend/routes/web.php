<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('index');
});
// This route is unreachable in the deployed application (the front hands off the request to GraphQL).
// This route is added to provide the the reverse routing to generate the URL in password reset emails.
Route::get('/reset-password/{token}', function (string $token) {
    return view('auth.reset-password', ['token' => $token]);
})->middleware('guest')->name('password.reset');

// Catch-all route for SPA deep linking. All unmatched routes serve the index
// view so that Vue Router can handle client-side routing. This must remain the
// last route defined in this file.
Route::get('/{any}', function () {
    return view('index');
})->where('any', '.*');
