<?php

namespace App\Http\Middleware;

use App\Token;
use Closure;
use Illuminate\Support\Facades\Validator;

class tokenValidator {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $receivedToken = $request->token;
        // Check is token isset
        $validator = validator::make($request->all(), [
            'token' => 'required'
        ]);
        // Return message if the validator goes wrong.
        if ($validator->fails())
        {
            return response(['result' => 'false', 'response' => $validator->errors()->all()[0]]);
        }
        // Check if the token is corresponding to the one in the database
        $checkTokenExists = Token::where('api_token', $request->token)->exists();

        // If not, return error message
        if (!$checkTokenExists)
        {
            return response(['result' => 'false', 'response' => 'The token is invalid']);
        }

        // Set up the time variable
        $now = time();
        $tokenInfo = Token::where('api_token', $receivedToken)->first();
        $tokenExpiryTime = $tokenInfo->expiry_time;
        $tokenRefreshTime = $tokenInfo->refresh_time;
        $user_id = $tokenInfo->user_id;

        // Check all of the tokens in database are expired, if so, delete all of them.
        Token::where('refresh_time', '<', $now)->delete();

        // If current time is greater than tokenExpiryTime but still lessen than tokenRefreshTime,
        // create a new one with new expiry_time and refresh_time, and delete the current one.
        // It's for security reason. I don't want a same token to exist in the client's side for too long.
        if (($now > $tokenExpiryTime) && ($now < $tokenRefreshTime))
        {
            // create a new token and make sure there is no any in the database with the same tokenName
            $updatedToken = Token::createAUniqueToken();

            // For security reason we update the current token name but still allow user to access the service
            Token::where('api_token', $receivedToken)
                ->update(['api_token'    => $updatedToken,
                          'expiry_time'  => $now + 7200,
                          'refresh_time' => $now + 84600
                ]);
            $request->attributes->add(['updatedToken' => $updatedToken]);
            $request->token = $updatedToken;

            return $next($request);
        }

        // If current time is lesser than either tokenExpiryTime or tokenRefreshTime
        // Update the expiry time and refresh time in the database.
        elseif (($now < $tokenExpiryTime) && ($now < $tokenRefreshTime))
        {
            Token::where('api_token', $receivedToken)
                ->update(['expiry_time' => $now + 7200, 'refresh_time' => $now + 84600]);

            return $next($request);
        }
        else{
            return response(['result' => 'false', 'response' => 'The token is invalid']);
        }


//        $now = time();
//        $expiry_time =
//
//
//
//        return $next($request);
    }
}
