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
Route::post('/checkIn', 'checkInController@checkIn');
Route::post('/showCheckIn', 'checkInController@showCheckIn');
Route::post('/adminShowTodayCheckIn', 'adminController@showCheckInToday')->middleware('admin');
Route::post('/adminShowSingleUserCheckIn', 'adminController@showSingleUserCheckIn')->middleware('admin');
