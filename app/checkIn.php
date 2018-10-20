<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class checkIn extends Model {

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
            } // if not, calculate from yesterday and go backwards.
            else
            {
                $count = checkIn::where('user_id', $user_id)->whereDay('created_at', Carbon::yesterday()->subDays($i)->day)->count();
            }

            $i = $i + 1;

        };


        return $i - 1;
    }

    public static function getDateAndCheckOrNotInformation($user_id)
    {
        // Get the Day and check_or_not information from check_ins table with designated user_id.
        return DB::table('check_ins')
            ->select(DB::raw('day(created_at)date, check_or_not'))
            ->whereMonth('created_at', Carbon::now()->month)
            ->where('user_id', $user_id)
            ->get()->toArray();
    }

    public static function getCheckedInBreakDownThisMonth($dateAndCheckOrNotInformation)
    {
        // Set an empty array for further usage.
        $checkInOrNotBreakDownEachDayThisMonth = array();

        // Get how many days in the current month
        $howManyDaysInAMonth = cal_days_in_month(CAL_GREGORIAN, Carbon::now()->month, Carbon::now()->year);

        // If the day information we might get from check_ins table does exist,
        // return the value of check_or_not column.
        for ($eachDayThisMonth = 1; $eachDayThisMonth <= $howManyDaysInAMonth; $eachDayThisMonth ++)
        {

            foreach ($dateAndCheckOrNotInformation as $singleDateAndCheckOrNot)
            {
                if ($eachDayThisMonth == $singleDateAndCheckOrNot->date)
                {
                    $checkInOrNotBreakDownEachDayThisMonth[$eachDayThisMonth] = $singleDateAndCheckOrNot->check_or_not;
                    break;
                }
            }

            // If not, return 'no' on that day.
            if (!isset($checkInOrNotBreakDownEachDayThisMonth[$eachDayThisMonth]))
            {
                // If the checked day is later than today, show 'to be seen'
                $checkInOrNotBreakDownEachDayThisMonth[$eachDayThisMonth] = $eachDayThisMonth > Carbon::now()->day ? 'To be seen' : 'no';
            }
        }
        return $checkInOrNotBreakDownEachDayThisMonth;

    }






}
