<?php

namespace App\Http\Controllers;

use App\CheckIn;
use App\Token;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class checkInController extends Controller {

    public function checkIn(Request $request)
    {
        // Get the user_id from tokens table.
        $user_id = Token::getUserIdThroughToken($request->token);

        // Check if the user has checked in
        $ifUserCheckedInToday = CheckIn::ifUserCheckInToday($user_id);

        // If so, response that the user has already checked in.
        if ($ifUserCheckedInToday)
        {
            return array_merge(
                $result = array('result' => 'true', 'response' => 'You already checked in today'),
                // If updatedToken does exist, also return with it.
                (($request->get('updatedToken') !== null)
                    ? array('updatedToken' => $request->get('updatedToken'))
                    : array()));
        }

        // If not, check the user in.
        checkIn::forceCreate([
            'user_id'      => $user_id,
            'check_or_not' => 'checked',
        ]);

        // Response the successfully checked in message.
        return array_merge(array('result' => 'true', 'response' => 'You\'ve successfully checked in'),
            (($request->get('updatedToken') !== null)
                ? array('updatedToken' => $request->get('updatedToken'))
                : array()));
    }


    public function showCheckIn(Request $request)
    {
        // Set the time variable for further usage.
        $time = Carbon::now();
        $thisYear = $time->year;
        $thisMonth = $time->month;
        $today = $time->day;

        // Get the user_id through token
        $user_id = Token::getUserIdThroughToken($request->token);

        // Get the check in record and check in date of designated user_id
        $checkInInDetail = DB::table('check_ins')
            ->select(DB::raw('day(created_at)date, check_or_not'))
            ->whereMonth('created_at', $thisMonth)
            ->where('user_id', $user_id)
            ->get()->toArray();

        // Set an empty array for further usage
        $finalOutput = array();

        // Get how many days in this month
        $howManyDaysInAMonth = cal_days_in_month(CAL_GREGORIAN, $thisMonth, $thisYear);


        // If the day information we might get from check_ins table does exist,
        // return the value of check_or_not column.
        for ($daysInAMonth = 1; $daysInAMonth <= $howManyDaysInAMonth; $daysInAMonth ++)
        {
            foreach ($checkInInDetail as $data)
            {
                if ($daysInAMonth == $data->date)
                {
                    $finalOutput[$daysInAMonth] = $data->check_or_not;
                    break;
                }
            }
            // If not, set the key as the date, and the value as no
            if (!isset($finalOutput[$daysInAMonth]))
            {
                // If the checked day is later than today, show 'to be seen'
                $finalOutput[$daysInAMonth] = $daysInAMonth > $today ? 'To be seen' : 'no';
            }
        }

        // return the result.
        return array_merge(
            array('result' => 'true', 'response' => $finalOutput),
            // If updatedToken does exist, return updatedToken
            (($request->get('updatedToken') !== null) ? array('updatedToken' => $request->get('updatedToken')) : array()));

    }

    public function consecutiveCheckInCount(Request $request)
    {
        // Get user_id through token
        $user_id = Token::getUserIdThroughToken($request->token);

        // check if the user has checked in or not
        $todayCheckInExists = CheckIn::ifUserCheckInToday($user_id);

        // if the user has checked in today, calculate how many days the user has checked in consecutively, starting from today and go backwards.
        // if the check-in record exists, keep calculating.
        // if not, calculate from yesterday and go backwards.
        $consecutivelyCheckingInDays = CheckIn::daysTheUserHasCheckedInConsecutively($user_id, $todayCheckInExists);

        // Check if it should day or days.
        $dayOrDays = str_plural('day', $consecutivelyCheckingInDays);


        // if the user has checked in today, return the message with how many days the user has checked in until today.
        // If not, also return with how many days the user has checked in until yesterday.
        return array_merge(
            array('result' => 'true', 'response' =>
                ($todayCheckInExists ? 'You already checked in today, and you\'ve consecutively checked in for ' : 'You haven\'t checked in today, and you\'ve consecutively checked in for ')
                . $consecutivelyCheckingInDays
                . ' '
                . $dayOrDays),
            // If updatedToken does exist, return updatedToken
            (($request->get('updatedToken') !== null)
                ? array('updatedToken' => $request->get('updatedToken'))
                : array()));
    }
}
