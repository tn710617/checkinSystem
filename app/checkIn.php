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

    public static function daysTheUserHasCheckedInConsecutively($user_id, $todayCheckInExists)
    {
        // if the user has checked in today, calculate how many days the user has checked in consecutively, starting from today and go backwards.
        $i = 0;
        $count = 1;
        // if the count == 1, keep calculating.
        while ($count == 1)
        {
            if ($todayCheckInExists)
            {
                $count = checkIn::where('user_id', $user_id)->whereDay('created_at', Carbon::now()->subDays($i)->day)->count();
            }
            // if not, calculate from yesterday and go backwards.
            else
            {
                $count = checkIn::where('user_id', $user_id)->whereDay('created_at', Carbon::yesterday()->subDays($i)->day)->count();
            }

            $i = $i + 1;

        };


        return $i-1;
    }
}
