<?php

namespace App\Http\Controllers;

use App\checkIn;
use App\Token;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class checkInController extends Controller
{
    public function checkIn(Request $request)
    {
        $this->validate(request(),[
            'token' => 'required',
        ]);
        $token_count = token::where('api_token', $request->token)->count();
        if(!$token_count)
        {
            return ['result' => 'false', 'response' => 'Please login before checking in'];
        }
        $user_id = token::where('api_token', $request->token)->first()->user_id;
        $userInfo = User::where('id', $user_id)->first();

        $currentDate = Carbon::now()->day;
        $checkInCount = checkIn::whereday('created_at', $currentDate)->where('user_id', $user_id)->count();

        if($checkInCount)
        {
            return ['result' => 'false', 'response' => 'You already checked in today'];
        }

        checkIn::forceCreate([
            'user_id' => $userInfo->id,
            'check' => 'yes',
        ]);

        return ['result' => 'true', 'response' => 'You\'ve successfully checked in'];
    }

}
