<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class checkIn extends Model
{
    public static function ifUserCheckInToday($user_id)
    {
        return static::where('user_id', $user_id)
            ->whereDay('created_at', Carbon::now()->day)
            ->exists();
    }

}
