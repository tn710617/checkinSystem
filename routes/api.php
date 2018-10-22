<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api.Ray')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/register', 'RegistrationController@register');
Route::post('/login', 'LoginController@login');
Route::delete('/logout', 'SessionController@logout')->middleware('tokenValidator');
Route::put('/update', 'SessionController@update')->middleware('tokenValidator');
Route::post('/checkIn', 'CheckInController@checkIn')->middleware('tokenValidator');
Route::post('/showCheckIn', 'CheckInController@showCheckIn')->middleware('tokenValidator');
Route::post('/adminShowTodayCheckIn', 'AdminController@showCheckInToday')->middleware('admin', 'tokenValidator');
Route::post('/adminShowSingleUserCheckIn', 'AdminController@showSingleUserCheckIn')->middleware('admin', 'tokenValidator');





//Route::post('/consecutiveCheckIn', 'checkInController@consecutiveCheckInCount')->middleware('TokenValidator');
