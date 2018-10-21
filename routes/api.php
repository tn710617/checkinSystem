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
Route::post('/register', 'registrationController@register');
Route::post('/login', 'loginController@login');
Route::delete('/logout', 'sessionController@logout')->middleware('tokenValidator');
Route::put('/update', 'sessionController@update')->middleware('tokenValidator');
Route::post('/checkIn', 'checkInController@checkIn')->middleware('tokenValidator');
Route::post('/showCheckIn', 'checkInController@showCheckIn')->middleware('tokenValidator');
Route::post('/adminShowTodayCheckIn', 'adminController@showCheckInToday')->middleware('admin', 'tokenValidator');
Route::post('/adminShowSingleUserCheckIn', 'adminController@showSingleUserCheckIn')->middleware('admin', 'tokenValidator');





//Route::post('/consecutiveCheckIn', 'checkInController@consecutiveCheckInCount')->middleware('tokenValidator');
