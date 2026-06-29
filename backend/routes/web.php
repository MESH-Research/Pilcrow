<?php
declare(strict_types=1);

use App\Http\Controllers\AvatarReportSnapshotController;
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
Route::get('/reset-password/{token}', function () {
    return view('index');
})->name('password.reset');

// Moderator-gated stream of a reported avatar's retained private snapshot.
// Defined before the SPA catch-all so it is reachable. Authorization lives in
// the controller (the admin_avatar_moderate ability), which denies guests too —
// so we deliberately omit the `auth` middleware: as an <img> load it doesn't
// expect JSON, so `auth` would try to redirect an expired session to the
// (nonexistent) `login` route and 500 instead of returning a clean 403.
Route::get(
    '/moderation/avatar-reports/{avatarReport}/snapshot',
    [AvatarReportSnapshotController::class, 'show']
)->name('avatar-report.snapshot');

// Catch-all route for SPA deep linking. All unmatched routes serve the index
// view so that Vue Router can handle client-side routing. This must remain the
// last route defined in this file.
Route::get('/{any}', function () {
    return view('index');
})->where('any', '.*');
