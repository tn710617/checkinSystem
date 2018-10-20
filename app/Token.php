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
}
