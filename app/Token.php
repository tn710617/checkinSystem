<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    public static function getUserIdThroughToken($token)
    {
        return static::where('api_token', $token)->first()->user_id;
    }
    //
    public static function createAUniqueToken()
    {
        $checkTokenCount = 1;
        while ($checkTokenCount)
        {
            $uniqueToken = str_random(60);
            $checkTokenCount = Token::where('api_token', $uniqueToken)->count();
        }
        return $uniqueToken;
    }

}
