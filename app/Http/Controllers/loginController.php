<?php

namespace App\Http\Controllers;

use App\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class loginController extends Controller {

    public function login(Request $request)
    {
        $validator = validator::make($request->all(), [
            'email'    => 'required|string|max:255',
            'password' => 'required|string|max:255'
        ]);
        if ($validator->fails())
        {
            return ['result' => 'false', 'response' => $validator->errors()->first()];
        }

        if (!Auth::attempt(request(['email', 'password'])))
        {
            return ['result' => 'false', 'response' => 'Please check your credentials and try again'];
        }

        $token_count = 1;
        while ($token_count == 1)
        {
            $token = str_random(60);
            $token_count = DB::table('tokens')->where('api_token', $token)->count();
        }

        $user = Auth::user();
        token::forceCreate([
            'user_id'      => $user->id,
            'api_token'    => $token,
            'expiry_time'  => time() + 7200,
            'refresh_time' => time() + 84600
        ]);

        return ['result' => 'true', 'response' => 'You\'ve successfully logged in', 'token' => $token];

    }
}
