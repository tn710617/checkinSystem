<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CheckIn extends Model {

    public static function ifUserCheckInToday($user_id)
    {
        return static::where('user_id', $user_id)
            ->whereDate('created_at', Carbon::today())
            ->exists();
    }

    public static function getDateAndCheckOrNotInformation($user_id)
    {
        // Get the Day and check_or_not information from check_ins table with designated user_id.
        return DB::table('check_ins')
            ->select(DB::raw('day(created_at)date, check_or_not'))
            ->whereYear('created_at', Carbon::now()->year)
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

    public static function howManyDaysTheUserHasCheckedInConsecutivelyUntilToday($user_id)
    {
        $i = 0;
        $count = 1;
        // if the count == 1, keep calculating.
        while ($count == 1)
        {
            $count = checkIn::where('user_id', $user_id)
                ->whereDate('created_at', Carbon::today()->subDays($i))
                ->count();
            $i ++;
        }

        return $i - 1;
    }

    public static function howManyDaysTheUserHasCheckedInConsecutivelyUntilYesterday($user_id)
    {

        $i = 0;
        $count = 1;
        // if the count == 1, keep calculating.
        while ($count == 1)
        {
            $count = checkIn::where('user_id', $user_id)
                ->whereDate('created_at', Carbon::yesterday()->subDays($i))
                ->count();
            $i ++;
        }

        return $i - 1;

    }

    public static function singularOrPlural($word, $number)
    {
         return str_plural($word, $number);
    }
}
